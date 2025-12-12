<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    ticketId: Number,
});

const currentUser = usePage().props.auth.user;
const activeCall = ref(null);
const localStream = ref(null);
const remoteStream = ref(null);
const peerConnection = ref(null);
const localVideo = ref(null);
const remoteVideo = ref(null);
const callStatus = ref('idle'); // idle, initiating, ringing, connected, ended
const isMuted = ref(false);
const isVideoOff = ref(false);

const configuration = {
    iceServers: [
        { urls: 'stun:stun.l.google.com:19302' },
        { urls: 'stun:stun1.l.google.com:19302' },
    ]
};

const startCall = async (type) => {
    try {
        callStatus.value = 'initiating';
        
        // Create call message
        const response = await axios.post(route('ticket.calls.start', props.ticketId), {
            call_type: type,
        });
        
        activeCall.value = response.data;
        
        // Get user media
        localStream.value = await navigator.mediaDevices.getUserMedia({
            audio: true,
            video: type === 'video',
        });
        
        if (localVideo.value) {
            localVideo.value.srcObject = localStream.value;
        }
        
        // Create peer connection
        await createPeerConnection();
        
        // Add tracks to peer connection
        localStream.value.getTracks().forEach(track => {
            peerConnection.value.addTrack(track, localStream.value);
        });
        
        // Create and send offer
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
        alert('Failed to start call. Please check your camera/microphone permissions.');
        endCall();
    }
};

const answerCall = async (callMessage) => {
    try {
        callStatus.value = 'initiating';
        activeCall.value = callMessage;
        
        // Get user media
        localStream.value = await navigator.mediaDevices.getUserMedia({
            audio: true,
            video: callMessage.call_type === 'video',
        });
        
        if (localVideo.value) {
            localVideo.value.srcObject = localStream.value;
        }
        
        // Create peer connection
        await createPeerConnection();
        
        // Add tracks
        localStream.value.getTracks().forEach(track => {
            peerConnection.value.addTrack(track, localStream.value);
        });
        
        // Update status to in_progress
        await axios.patch(route('ticket.calls.status', {
            ticket: props.ticketId,
            messageId: callMessage.id
        }), {
            status: 'in_progress'
        });
        
        callStatus.value = 'connected';
        
    } catch (error) {
        console.error('Error answering call:', error);
        alert('Failed to answer call. Please check your camera/microphone permissions.');
        declineCall(callMessage);
    }
};

const declineCall = async (callMessage) => {
    await axios.patch(route('ticket.calls.status', {
        ticket: props.ticketId,
        messageId: callMessage.id
    }), {
        status: 'declined'
    });
    
    if (activeCall.value?.id === callMessage.id) {
        endCall();
    }
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
    
    // Stop all tracks
    if (localStream.value) {
        localStream.value.getTracks().forEach(track => track.stop());
        localStream.value = null;
    }
    
    if (remoteStream.value) {
        remoteStream.value.getTracks().forEach(track => track.stop());
        remoteStream.value = null;
    }
    
    // Close peer connection
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
    
    // Handle remote stream
    peerConnection.value.ontrack = (event) => {
        if (!remoteStream.value) {
            remoteStream.value = new MediaStream();
            if (remoteVideo.value) {
                remoteVideo.value.srcObject = remoteStream.value;
            }
        }
        remoteStream.value.addTrack(event.track);
    };
    
    // Handle ICE candidates
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
    
    // Handle connection state changes
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

// Listen for signaling events
onMounted(() => {
    if (window.Echo) {
        Echo.private(`ticket.${props.ticketId}`)
            .listen('NewMessageEvent', async (e) => {
                const message = e.message;
                
                // Check if it's a call message
                if (message.call_type && message.user_id !== currentUser.id && message.call_status === 'initiated') {
                    // Show incoming call UI
                    const accept = confirm(`Incoming ${message.call_type} call from ${message.user.name}. Accept?`);
                    if (accept) {
                        await answerCall(message);
                    } else {
                        await declineCall(message);
                    }
                }
            })
            .listen('CallSignalingEvent', async (e) => {
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
            });
    }
});

onUnmounted(() => {
    endCall();
});
</script>

<template>
    <div>
        <!-- Call Controls Bar -->
        <div class="bg-white rounded-lg shadow-md p-4 mb-4 flex items-center justify-between">
            <div class="flex gap-2">
                <button 
                    @click="startCall('voice')" 
                    :disabled="callStatus !== 'idle'"
                    class="flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                    </svg>
                    Voice Call
                </button>
                
                <button 
                    @click="startCall('video')" 
                    :disabled="callStatus !== 'idle'"
                    class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                    Video Call
                </button>
            </div>
            
            <div v-if="callStatus !== 'idle'" class="flex items-center gap-2">
                <span class="text-sm font-medium" :class="{
                    'text-yellow-600': callStatus === 'initiating' || callStatus === 'ringing',
                    'text-green-600': callStatus === 'connected',
                }">
                    {{ callStatus === 'initiating' ? 'Starting...' : 
                       callStatus === 'ringing' ? 'Ringing...' : 
                       callStatus === 'connected' ? 'Connected' : 'Call ended' }}
                </span>
                
                <div v-if="callStatus === 'connected'" class="flex gap-2">
                    <button 
                        @click="toggleMute"
                        class="p-2 rounded-full transition"
                        :class="isMuted ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path v-if="!isMuted" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path>
                            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                    
                    <button 
                        v-if="activeCall?.call_type === 'video'"
                        @click="toggleVideo"
                        class="p-2 rounded-full transition"
                        :class="isVideoOff ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                    </button>
                </div>
                
                <button 
                    @click="endCall"
                    class="flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    End Call
                </button>
            </div>
        </div>
        
        <!-- Video Display -->
        <div v-if="activeCall?.call_type === 'video' && callStatus !== 'idle'" class="relative bg-gray-900 rounded-lg overflow-hidden" style="height: 500px;">
            <!-- Remote Video (Large) -->
            <video 
                ref="remoteVideo" 
                autoplay 
                playsinline
                class="w-full h-full object-cover"
            ></video>
            
            <!-- Local Video (Small PIP) -->
            <div class="absolute bottom-4 right-4 w-48 h-36 bg-gray-800 rounded-lg overflow-hidden shadow-lg border-2 border-white">
                <video 
                    ref="localVideo" 
                    autoplay 
                    playsinline 
                    muted
                    class="w-full h-full object-cover"
                ></video>
            </div>
            
            <!-- Status Overlay -->
            <div v-if="callStatus !== 'connected'" class="absolute inset-0 bg-black bg-opacity-75 flex items-center justify-center">
                <div class="text-center text-white">
                    <div class="w-16 h-16 border-4 border-white border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
                    <p class="text-xl font-semibold">{{ callStatus === 'ringing' ? 'Calling...' : 'Connecting...' }}</p>
                </div>
            </div>
        </div>
        
        <!-- Audio Call Display -->
        <div v-else-if="activeCall?.call_type === 'voice' && callStatus !== 'idle'" class="bg-gradient-to-br from-green-600 to-green-800 rounded-lg p-12 text-center text-white">
            <div class="w-24 h-24 bg-white bg-opacity-20 rounded-full mx-auto mb-6 flex items-center justify-center">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
            </div>
            <h3 class="text-2xl font-bold mb-2">Voice Call</h3>
            <p class="text-lg opacity-90">{{ callStatus === 'ringing' ? 'Calling...' : callStatus === 'connected' ? 'In call' : 'Connecting...' }}</p>
            
            <!-- Hidden audio element for remote stream -->
            <audio ref="remoteVideo" autoplay playsinline class="hidden"></audio>
        </div>
    </div>
</template>
