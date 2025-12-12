<script setup>
import { ref, onMounted, onUnmounted, nextTick, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    ticketId: Number,
    initialMessages: Array,
    initialMessagesCount: { type: Number, default: 0 },
});

const messages = ref([...props.initialMessages]);
const messageContainer = ref(null);
const fileInput = ref(null);
const currentUser = usePage().props.auth.user;
const formMessage = ref('');

// --- Upload & Drag State ---
const attachment = ref(null);      // The actual File object
const attachmentPreview = ref(null); // The blob URL for preview
const isDragging = ref(false);     // UI state for drop zone

// --- Existing State ---
const typingUsers = ref([]);
const lastTypingTime = ref(0);
const currentPage = ref(1);
const perPage = ref(20);
const totalMessages = ref(props.initialMessagesCount || messages.value.length || 0);
const loadingMore = ref(false);
const toastMessage = ref('');
const isNearBottom = ref(true);

// --- Call State ---
const activeCall = ref(null);
const localStream = ref(null);
const remoteStream = ref(null);
const peerConnection = ref(null);
const localVideo = ref(null);
const remoteVideo = ref(null);
const remoteAudio = ref(null);
const callStatus = ref('idle'); // idle, initiating, ringing, connected, ended
const isMuted = ref(false);
const isVideoOff = ref(false);
const incomingCall = ref(null);

const configuration = {
    iceServers: [
        { urls: 'stun:stun.l.google.com:19302' },
        { urls: 'stun:stun1.l.google.com:19302' },
    ]
};

const hasMore = computed(() => messages.value.length < totalMessages.value);

// --- Scroll Logic ---
const checkScrollPosition = () => {
    if (!messageContainer.value) return;
    const { scrollTop, scrollHeight, clientHeight } = messageContainer.value;
    isNearBottom.value = scrollHeight - scrollTop - clientHeight < 100;
};

const scrollToBottom = (force = false) => {
    nextTick(() => {
        if (messageContainer.value && (force || isNearBottom.value)) {
            messageContainer.value.scrollTop = messageContainer.value.scrollHeight;
        }
    });
};

// --- Drag & Drop Handlers ---
const onDragEnter = () => { isDragging.value = true; };
const onDragLeave = () => { isDragging.value = false; };

const onDrop = (e) => {
    isDragging.value = false;
    const files = e.dataTransfer.files;
    if (files.length > 0) processFile(files[0]);
};

const onFileSelect = (e) => {
    const files = e.target.files;
    if (files.length > 0) processFile(files[0]);
};

const processFile = (file) => {
    if (!file.type.startsWith('image/')) {
        showToast("Only image files are allowed.");
        return;
    }
    // Create a local preview URL
    attachment.value = file;
    attachmentPreview.value = URL.createObjectURL(file);
};

const removeAttachment = () => {
    attachment.value = null;
    attachmentPreview.value = null;
    if (fileInput.value) fileInput.value.value = ''; // Reset input
};

// --- Messaging Logic ---
const sendTyping = () => {
    const now = Date.now();
    if (now - lastTypingTime.value > 2000) {
        try {
            Echo.private(`ticket.${props.ticketId}`).whisper('typing', {
                user_id: currentUser.id, name: currentUser.name,
            });
            lastTypingTime.value = now;
        } catch (e) {}
    }
};

const submitMessage = async () => {
    const text = (formMessage.value || '').trim();
    const hasFile = !!attachment.value;

    if (!text && !hasFile) return;

    const tempId = `temp-${Date.now()}`;
    
    // Optimistic Message Object
    const tempMessage = {
        id: tempId,
        ticket_id: props.ticketId,
        user_id: currentUser.id,
        message: text,
        attachment_url: attachmentPreview.value, // Display local blob immediately
        created_at: new Date().toISOString(),
        user: { id: currentUser.id, name: currentUser.name },
        optimistic: true,
    };

    messages.value.push(tempMessage);
    scrollToBottom(true);
    
    // Reset Form Immediately
    formMessage.value = '';
    const fileToSend = attachment.value; // capture before reset
    removeAttachment();

    try {
        // Prepare FormData for file upload
        const formData = new FormData();
        if (text) formData.append('message', text);
        if (fileToSend) formData.append('attachment', fileToSend);

        const res = await axios.post(route('ticket.messages.store', props.ticketId), formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        
        const realMessage = res.data;

        // Swap optimistic message with real one
        const idx = messages.value.findIndex(m => m.id === tempId);
        if (idx !== -1) messages.value.splice(idx, 1, realMessage);
        else messages.value.push(realMessage);

    } catch (err) {
        // Remove optimistic message on failure
        const idx = messages.value.findIndex(m => m.id === tempId);
        if (idx !== -1) messages.value.splice(idx, 1);
        console.error(err);
        showToast('Failed to send message');
    }
};

const loadMore = async () => {
    if (loadingMore.value || !hasMore.value) return;
    loadingMore.value = true;
    
    const container = messageContainer.value;
    const oldScrollHeight = container ? container.scrollHeight : 0;
    const oldScrollTop = container ? container.scrollTop : 0;

    try {
        const url = route('ticket.messages.index', props.ticketId) + `?page=${currentPage.value + 1}&per_page=${perPage.value}`;
        const res = await axios.get(url);
        const items = res.data.data || [];
        
        if (items.length) {
            messages.value = [...items, ...messages.value];
            currentPage.value++;
            nextTick(() => {
                if (container) container.scrollTop = container.scrollHeight - oldScrollHeight + oldScrollTop;
            });
        }
    } catch (e) {
        showToast('Failed to load history');
    } finally {
        loadingMore.value = false;
    }
};

const showToast = (msg = '') => {
    toastMessage.value = msg;
    setTimeout(() => toastMessage.value = '', 3500);
};

// --- Call Functions ---
const startCall = async (type) => {
    try {
        callStatus.value = 'initiating';
        console.log('[Call] startCall invoked', { type, ticketId: props.ticketId });
        
        const response = await axios.post(route('ticket.calls.start', props.ticketId), {
            call_type: type,
        });
        
        activeCall.value = response.data;
        
        try {
            console.log('[Call] Requesting getUserMedia', { audio: true, video: type === 'video' });
            localStream.value = await navigator.mediaDevices.getUserMedia({
                audio: true,
                video: type === 'video',
            });
            console.log('[Call] getUserMedia success', localStream.value);
        } catch (gumErr) {
            console.error('[Call] getUserMedia failed', gumErr);
            throw gumErr;
        }
        
        if (localVideo.value) {
            localVideo.value.srcObject = localStream.value;
        }
        
        await createPeerConnection();
        
        localStream.value.getTracks().forEach(track => {
            peerConnection.value.addTrack(track, localStream.value);
        });
        
        const offer = await peerConnection.value.createOffer();
        await peerConnection.value.setLocalDescription(offer);
        
        await axios.post(route('ticket.calls.offer', { 
            ticket: props.ticketId, 
            messageId: activeCall.value.id 
        }), {
            offer: {
                type: offer.type,
                sdp: offer.sdp,
            }
        });
        
        callStatus.value = 'ringing';
        
    } catch (error) {
        console.error('Error starting call:', error);
        showToast('Failed to start call. Please check your permissions.');
        endCall();
    }
};

const answerCall = async () => {
    if (!incomingCall.value) return;
    
    try {
        callStatus.value = 'initiating';
        activeCall.value = incomingCall.value;
        incomingCall.value = null;
        
        localStream.value = await navigator.mediaDevices.getUserMedia({
            audio: true,
            video: activeCall.value.call_type === 'video',
        });
        
        if (localVideo.value) {
            localVideo.value.srcObject = localStream.value;
        }
        
        await createPeerConnection();
        
        localStream.value.getTracks().forEach(track => {
            peerConnection.value.addTrack(track, localStream.value);
        });
        
        await axios.patch(route('ticket.calls.status', {
            ticket: props.ticketId,
            messageId: activeCall.value.id
        }), {
            status: 'in_progress'
        });
        
        callStatus.value = 'connected';
        
    } catch (error) {
        console.error('Error answering call:', error);
        showToast('Failed to answer call. Please check your permissions.');
        declineCall();
    }
};

const declineCall = async () => {
    if (!incomingCall.value) return;
    
    await axios.patch(route('ticket.calls.status', {
        ticket: props.ticketId,
        messageId: incomingCall.value.id
    }), {
        status: 'declined'
    });
    
    incomingCall.value = null;
};

const endCall = async () => {
    if (activeCall.value) {
        try {
            await axios.put(route('ticket.calls.end', {
                ticket: props.ticketId,
                messageId: activeCall.value.id
            }));
        } catch (error) {
            console.error('Error ending call:', error);
        }
    }
    
    if (localStream.value) {
        localStream.value.getTracks().forEach(track => track.stop());
        localStream.value = null;
    }
    
    if (remoteStream.value) {
        remoteStream.value.getTracks().forEach(track => track.stop());
        remoteStream.value = null;
    }
    
    if (peerConnection.value) {
        peerConnection.value.close();
        peerConnection.value = null;
    }
    
    activeCall.value = null;
    callStatus.value = 'idle';
    isMuted.value = false;
    isVideoOff.value = false;
};

const createPeerConnection = async () => {
    peerConnection.value = new RTCPeerConnection(configuration);
    
    peerConnection.value.ontrack = (event) => {
        if (!remoteStream.value) {
            remoteStream.value = new MediaStream();
            if (remoteVideo.value) {
                remoteVideo.value.srcObject = remoteStream.value;
            }
            if (remoteAudio.value) {
                remoteAudio.value.srcObject = remoteStream.value;
            }
        }
        remoteStream.value.addTrack(event.track);
    };
    
    peerConnection.value.onicecandidate = async (event) => {
        if (event.candidate && activeCall.value) {
            try {
                await axios.post(route('ticket.calls.ice', {
                    ticket: props.ticketId,
                    messageId: activeCall.value.id
                }), {
                    candidate: {
                        candidate: event.candidate.candidate,
                        sdpMLineIndex: event.candidate.sdpMLineIndex,
                        sdpMid: event.candidate.sdpMid,
                    }
                });
            } catch (error) {
                console.error('Error sending ICE candidate:', error);
            }
        }
    };
    
    peerConnection.value.onconnectionstatechange = () => {
        if (peerConnection.value.connectionState === 'connected') {
            callStatus.value = 'connected';
        } else if (peerConnection.value.connectionState === 'failed' || 
                   peerConnection.value.connectionState === 'disconnected') {
            endCall();
        }
    };
};

const toggleMute = () => {
    if (localStream.value) {
        const audioTrack = localStream.value.getAudioTracks()[0];
        if (audioTrack) {
            audioTrack.enabled = !audioTrack.enabled;
            isMuted.value = !audioTrack.enabled;
        }
    }
};

const toggleVideo = () => {
    if (localStream.value) {
        const videoTrack = localStream.value.getVideoTracks()[0];
        if (videoTrack) {
            videoTrack.enabled = !videoTrack.enabled;
            isVideoOff.value = !videoTrack.enabled;
        }
    }
};

onMounted(() => {
    console.log('[TicketChat] mounted', { ticketId: props.ticketId });
    scrollToBottom(true);

    if (!window.Echo) {
        console.error('[TicketChat] window.Echo is not available');
        return;
    }

    try {
        console.log('[TicketChat] Echo exists', { socketId: window.Echo.socketId ? window.Echo.socketId() : null, echo: window.Echo });
    } catch (err) {
        console.warn('[TicketChat] Echo socketId() threw', err);
    }

    const channel = Echo.private(`ticket.${props.ticketId}`);
    console.log('[TicketChat] subscribed to channel', channel);

    channel
        .listen('NewMessageEvent', (e) => {
            try {
                console.log('[Echo] NewMessageEvent', e?.message);
            } catch {}
            if (!messages.value.find(m => m.id === e.message.id)) {
                messages.value.push(e.message);
                scrollToBottom(false);
                
                // Check if it's an incoming call
                if (e.message.call_type && e.message.user_id !== currentUser.id && e.message.call_status === 'initiated') {
                    incomingCall.value = e.message;
                }
            }
        })
        .listen('CallSignalingEvent', async (e) => {
            try {
                console.log('[Echo] CallSignalingEvent', { type: e?.type, message_id: e?.message_id });
            } catch {}
            if (e.sender_id === currentUser.id || !activeCall.value) return;
            
            if (!peerConnection.value) {
                await createPeerConnection();
            }
            
            try {
                if (e.type === 'offer') {
                    await peerConnection.value.setRemoteDescription(new RTCSessionDescription(e.data));
                    const answer = await peerConnection.value.createAnswer();
                    await peerConnection.value.setLocalDescription(answer);
                    
                    await axios.post(route('ticket.calls.answer', {
                        ticket: props.ticketId,
                        messageId: e.message_id
                    }), {
                        answer: {
                            type: answer.type,
                            sdp: answer.sdp,
                        }
                    });
                } else if (e.type === 'answer') {
                    await peerConnection.value.setRemoteDescription(new RTCSessionDescription(e.data));
                    callStatus.value = 'connected';
                } else if (e.type === 'ice') {
                    await peerConnection.value.addIceCandidate(new RTCIceCandidate(e.data));
                }
            } catch (error) {
                console.error('Error handling signaling:', error);
            }
        })
        .listenForWhisper('typing', (e) => {
            if (!e || e.user_id === currentUser.id) return;
            const existing = typingUsers.value.find(u => u.user_id === e.user_id);
            existing ? existing._ts = Date.now() : typingUsers.value.push({ ...e, _ts: Date.now() });
        });

    const typingInterval = setInterval(() => {
        const now = Date.now();
        typingUsers.value = typingUsers.value.filter(u => (now - u._ts) < 3000);
    }, 1000);
});

onUnmounted(() => {
    // Cleanup when component unmounts
    endCall();
    Echo.leave(`ticket.${props.ticketId}`);
});
</script>

<template>
    <div 
        class="bg-white flex flex-col h-[650px] rounded-lg shadow-lg border border-gray-200 mt-6 overflow-hidden relative"
        @dragenter.prevent="onDragEnter" 
        @dragover.prevent 
        @dragleave.prevent="onDragLeave"
        @drop.prevent="onDrop"
    >
        
        <div v-if="isDragging" class="absolute inset-0 bg-blue-50 bg-opacity-90 z-50 flex flex-col items-center justify-center border-4 border-dashed border-blue-400 rounded-lg transition-all">
            <svg class="w-16 h-16 text-blue-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
            <p class="text-xl font-bold text-blue-600">Drop file to attach</p>
        </div>

        <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 flex justify-between items-center z-10">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Ticket #{{ ticketId }}</h3>
                <p class="text-xs text-gray-500 flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-green-500"></span> Online</p>
            </div>
            
            <!-- Call Controls -->
            <div v-if="callStatus === 'idle'" class="flex gap-2">
                <button @click="startCall('voice')" class="p-2 hover:bg-gray-200 rounded-full transition" title="Voice Call">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                    </svg>
                </button>
                <button @click="startCall('video')" class="p-2 hover:bg-gray-200 rounded-full transition" title="Video Call">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                </button>
            </div>
            <div v-else class="flex items-center gap-2">
                <span class="text-xs font-medium px-2 py-1 rounded-full" :class="{
                    'bg-yellow-100 text-yellow-700': callStatus === 'initiating' || callStatus === 'ringing',
                    'bg-green-100 text-green-700': callStatus === 'connected'
                }">
                    {{ callStatus === 'initiating' ? 'Starting...' : callStatus === 'ringing' ? 'Ringing...' : 'In Call' }}
                </span>
                
                <button v-if="callStatus === 'connected'" @click="toggleMute" :class="isMuted ? 'bg-red-100 text-red-600' : 'bg-gray-200 text-gray-700'" class="p-2 rounded-full hover:opacity-80 transition" title="Toggle Mute">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path v-if="!isMuted" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path>
                        <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"></path>
                    </svg>
                </button>
                
                <button v-if="callStatus === 'connected' && activeCall?.call_type === 'video'" @click="toggleVideo" :class="isVideoOff ? 'bg-red-100 text-red-600' : 'bg-gray-200 text-gray-700'" class="p-2 rounded-full hover:opacity-80 transition" title="Toggle Video">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                </button>
                
                <button @click="endCall" class="p-2 bg-red-600 text-white rounded-full hover:bg-red-700 transition" title="End Call">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
        
        <!-- Incoming Call Modal -->
        <div v-if="incomingCall" class="absolute inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center">
            <div class="bg-white rounded-2xl p-8 max-w-sm w-full mx-4 text-center shadow-2xl">
                <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full mx-auto mb-4 flex items-center justify-center animate-pulse">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path v-if="incomingCall.call_type === 'video'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">{{ incomingCall.user?.name || 'Someone' }}</h3>
                <p class="text-gray-600 mb-6">Incoming {{ incomingCall.call_type }} call...</p>
                <div class="flex gap-3">
                    <button @click="declineCall" class="flex-1 py-3 px-6 bg-red-600 text-white rounded-xl hover:bg-red-700 transition font-semibold">
                        Decline
                    </button>
                    <button @click="answerCall" class="flex-1 py-3 px-6 bg-green-600 text-white rounded-xl hover:bg-green-700 transition font-semibold">
                        Accept
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Video Call UI -->
        <div v-if="activeCall?.call_type === 'video' && callStatus !== 'idle'" class="absolute inset-0 bg-gray-900 z-40">
            <video ref="remoteVideo" autoplay playsinline class="w-full h-full object-cover"></video>
            <div class="absolute bottom-4 right-4 w-40 h-28 bg-gray-800 rounded-lg overflow-hidden shadow-2xl border-2 border-white">
                <video ref="localVideo" autoplay playsinline muted class="w-full h-full object-cover"></video>
            </div>
            <div v-if="callStatus !== 'connected'" class="absolute inset-0 bg-black bg-opacity-75 flex items-center justify-center">
                <div class="text-center text-white">
                    <div class="w-16 h-16 border-4 border-white border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
                    <p class="text-xl font-semibold">{{ callStatus === 'ringing' ? 'Calling...' : 'Connecting...' }}</p>
                </div>
            </div>
        </div>
        
        <!-- Audio Call UI (hidden audio element) -->
        <audio v-if="activeCall?.call_type === 'voice'" ref="remoteAudio" autoplay class="hidden"></audio>

        <div ref="messageContainer" @scroll="checkScrollPosition" class="flex-1 overflow-y-auto bg-gray-50 p-4 space-y-4 z-10">
            <div class="flex justify-center h-8" v-if="hasMore">
                <button v-if="!loadingMore" @click.prevent="loadMore" class="text-xs text-blue-500 bg-blue-50 px-3 py-1 rounded-full">Load earlier</button>
                <div v-else class="w-5 h-5 border-2 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
            </div>

            <div v-for="(msg, index) in messages" :key="msg.id || index" class="flex flex-col w-full" :class="msg.user_id === currentUser.id ? 'items-end' : 'items-start'">
                <div class="flex max-w-[85%] md:max-w-[75%] gap-2" :class="msg.user_id === currentUser.id ? 'flex-row-reverse' : 'flex-row'">
                    <div class="flex-shrink-0 mt-auto">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold shadow-sm" :class="msg.user_id === currentUser.id ? 'bg-blue-600 text-white' : 'bg-white border border-gray-200 text-gray-700'">
                            {{ (msg.user && msg.user.name) ? msg.user.name.charAt(0).toUpperCase() : '?' }}
                        </div>
                    </div>
                    
                    <div class="flex flex-col" :class="msg.user_id === currentUser.id ? 'items-end' : 'items-start'">
                        <span class="text-xs font-medium text-gray-900 mb-1 px-1" v-if="msg.user_id !== currentUser.id">{{ msg.user ? msg.user.name : 'Unknown' }}</span>
                        
                        <div class="shadow-sm text-sm relative group overflow-hidden"
                             :class="[
                                msg.user_id === currentUser.id ? 'bg-blue-600 text-white rounded-2xl rounded-br-sm' : 'bg-white border border-gray-200 text-gray-800 rounded-2xl rounded-bl-sm',
                                msg.optimistic ? 'opacity-75' : 'opacity-100'
                             ]">
                             
                             <div v-if="msg.attachment_url" class="p-1 pb-0">
                                <img :src="msg.attachment_url" class="rounded-lg max-w-full max-h-64 object-cover cursor-pointer hover:opacity-90 transition" @click="window.open(msg.attachment_url, '_blank')">
                            </div>

                            <div v-if="msg.message" class="px-4 py-2 whitespace-pre-wrap leading-relaxed">{{ msg.message }}</div>

                            <div v-if="msg.optimistic" class="absolute bottom-1 right-2">
                                <svg class="w-3 h-3 text-white animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            </div>
                        </div>
                        <span class="text-[10px] text-gray-400 mt-1 px-1">{{ new Date(msg.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-4 bg-white border-t border-gray-200 z-10">
            <div v-if="attachmentPreview" class="mb-2 flex items-start">
                <div class="relative group">
                    <img :src="attachmentPreview" class="h-16 w-16 object-cover rounded-md border border-gray-200 shadow-sm">
                    <button @click="removeAttachment" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-0.5 shadow-md hover:bg-red-600 transition">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            </div>

            <div class="h-6 mb-1 pl-2">
                <div v-if="typingUsers.length" class="flex items-center gap-2 text-xs text-gray-500 font-medium animate-pulse">
                    {{ typingUsers.map(u => u.name).join(', ') }} is typing...
                </div>
            </div>

            <form @submit.prevent="submitMessage" class="flex gap-2 items-end">
                <button type="button" @click="fileInput && fileInput.value && fileInput.value.click()" class="h-[48px] w-[40px] flex items-center justify-center text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-6 h-6 transform rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                </button>
                
                <input type="file" ref="fileInput" class="hidden" accept="image/*" @change="onFileSelect">

                <div class="flex-1 relative">
                    <textarea v-model="formMessage" @keydown.enter.prevent="submitMessage" @input="sendTyping" rows="1" class="w-full border-gray-300 rounded-2xl px-4 py-3 shadow-sm focus:border-blue-500 focus:ring-blue-500 resize-none" placeholder="Type a message..." style="min-height: 48px; max-height: 120px;"></textarea>
                </div>
                
                <button type="submit" class="h-[48px] w-[48px] flex items-center justify-center bg-blue-600 text-white rounded-full hover:bg-blue-700 shadow-md transition disabled:opacity-50" :disabled="!formMessage.trim() && !attachment">
                    <svg class="w-5 h-5 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                </button>
            </form>
        </div>
    </div>
</template>