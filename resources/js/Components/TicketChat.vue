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
const page = usePage();
const currentUser = page.props.auth.user;
const formMessage = ref('');

// --- Upload & Drag State ---
const attachment = ref(null);      
const attachmentPreview = ref(null); 
const isDragging = ref(false);

// --- UI State ---
const showEmojiPicker = ref(false);
const showReactionPicker = ref(null); // message id for reaction picker
const emojiList = ['👍', '❤️', '😂', '😮', '😢', '😡', '🙏', '👏'];     
// Minimize state
const isMinimized = ref(false);
// localStorage key per ticket
const storageKey = `ticketchat_minimized_${props.ticketId}`;

// --- Existing State ---
const typingUsers = ref([]);
const lastTypingTime = ref(0);
const currentPage = ref(1);
const perPage = ref(20);
const totalMessages = ref(props.initialMessagesCount || messages.value.length || 0);
const loadingMore = ref(false);
const toastMessage = ref('');
const isNearBottom = ref(true);
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
    attachment.value = file;
    attachmentPreview.value = URL.createObjectURL(file);
};

const removeAttachment = () => {
    attachment.value = null;
    attachmentPreview.value = null;
    if (fileInput.value) fileInput.value.value = ''; 
};

const openFilePicker = () => {
    try {
        if (fileInput.value && fileInput.value.click) {
            fileInput.value.click();
        }
    } catch (e) {
        console.error('Failed to open file picker', e);
        showToast('Unable to open file picker');
    }
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
    
    const tempMessage = {
        id: tempId,
        ticket_id: props.ticketId,
        user_id: currentUser.id,
        message: text,
        attachment_url: attachmentPreview.value, 
        created_at: new Date().toISOString(),
        user: { id: currentUser.id, name: currentUser.name },
        optimistic: true,
    };

    messages.value.push(tempMessage);
    scrollToBottom(true);
    
    formMessage.value = '';
    const fileToSend = attachment.value; 
    removeAttachment();

    try {
        const formData = new FormData();
        if (text) formData.append('message', text);
        if (fileToSend) formData.append('attachment', fileToSend);

        const res = await axios.post(route('ticket.messages.store', props.ticketId), formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        
        const realMessage = res.data;
        const idx = messages.value.findIndex(m => m.id === tempId);
        if (idx !== -1) messages.value.splice(idx, 1, realMessage);
        else messages.value.push(realMessage);

    } catch (err) {
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

const addEmoji = (emoji) => {
    formMessage.value += emoji;
    showEmojiPicker.value = false;
};

const toggleReaction = async (messageId, emoji) => {
    const msg = messages.value.find(m => m.id === messageId);
    if (!msg) return;
    
    if (!msg.reactions) msg.reactions = {};
    if (!msg.reactions[emoji]) msg.reactions[emoji] = [];
    
    const userIndex = msg.reactions[emoji].indexOf(currentUser.id);
    if (userIndex > -1) {
        msg.reactions[emoji].splice(userIndex, 1);
        if (msg.reactions[emoji].length === 0) delete msg.reactions[emoji];
    } else {
        msg.reactions[emoji].push(currentUser.id);
    }
    
    showReactionPicker.value = null;
    
};


onMounted(() => {
    scrollToBottom(true);

    // initialize minimized state from localStorage
    try {
        const stored = localStorage.getItem(storageKey);
        if (stored === 'true') {
            isMinimized.value = true;
        }
    } catch (e) {
        // ignore
    }

    if (!window.Echo) return;

    Echo.private(`ticket.${props.ticketId}`)
        .listen('NewMessageEvent', (e) => {
            if (!messages.value.find(m => m.id === e.message.id)) {
                messages.value.push(e.message);
                scrollToBottom(false);
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

// Compute partner name: prefer the other user's name found in messages, fall back to ticket props
const partnerName = computed(() => {
    // find most recent message from other user
    for (let i = messages.value.length - 1; i >= 0; i--) {
        const m = messages.value[i];
        if (m && m.user && m.user.id && String(m.user.id) !== String(currentUser.id)) {
            return m.user.name || `${m.user.first_name || ''} ${m.user.last_name || ''}`.trim() || 'Unknown';
        }
    }

    // fallback to ticket props if present
    const ticket = page.props.ticket || null;
    if (ticket) {
        // If the current user is the assigned employee, partner is the customer
        if (ticket.customer && ticket.customer.first_name) {
            return `${ticket.customer.first_name} ${ticket.customer.last_name || ''}`.trim();
        }
        // Fallback to assigned_to (employee)
        if (ticket.assigned_to && (ticket.assigned_to.first_name || ticket.assigned_to.user_id)) {
            return ticket.assigned_to.first_name ? `${ticket.assigned_to.first_name} ${ticket.assigned_to.last_name || ''}`.trim() : (ticket.assigned_to.name || 'Unknown');
        }
    }

    return 'Unknown';
});

onUnmounted(() => {
    if (window.Echo) {
        Echo.leave(`ticket.${props.ticketId}`);
    }
});

const toggleMinimize = () => {
    isMinimized.value = !isMinimized.value;
    if (!isMinimized.value) nextTick(() => scrollToBottom(true));
    try {
        localStorage.setItem(storageKey, isMinimized.value ? 'true' : 'false');
    } catch (e) {}
};
</script>

<template>
    <div 
        :class="[
            'bg-white flex flex-col rounded-lg shadow-lg border border-gray-200 mt-6 overflow-hidden transition-all',
            isMinimized ? 'fixed right-6 bottom-6 w-64 h-12 rounded-full z-50' : 'h-[650px] w-full relative'
        ]"
        @dragenter.prevent="onDragEnter" 
        @dragover.prevent 
        @dragleave.prevent="onDragLeave"
        @drop.prevent="onDrop"
    >
        
        <div v-if="isDragging" class="absolute inset-0 bg-blue-50 bg-opacity-90 z-50 flex flex-col items-center justify-center border-4 border-dashed border-blue-400 rounded-lg transition-all">
            <svg class="w-16 h-16 text-blue-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
            <p class="text-xl font-bold text-blue-600">Drop file to attach</p>
        </div>

        <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-4 py-2 border-b border-gray-300 flex justify-between items-center z-10 shadow-sm">
            <div class="flex items-center gap-3">
                <div :class="isMinimized ? 'w-8 h-8 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white font-bold text-sm shadow-md' : 'w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white font-bold text-lg shadow-md'">
                    {{ partnerName.charAt(0).toUpperCase() }}
                </div>
                <div v-if="!isMinimized">
                    <div class="flex items-center gap-1">
                        <h3 class="text-base font-semibold text-gray-800">{{ partnerName }}</h3>
                        <svg class="w-3 h-3 text-gray-500 cursor-pointer hover:text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                    <p class="text-xs text-gray-500 flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-green-500"></span>
                        <span>Online</span>
                    </p>
                </div>
                <div v-else class="text-sm font-medium truncate w-36">{{ partnerName }}</div>
            </div>
            <div class="flex items-center gap-2">
                <button v-if="!isMinimized" class="p-2 hover:bg-gray-200 rounded-full transition" title="Voice Call">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                </button>
                <button v-if="!isMinimized" class="p-2 hover:bg-gray-200 rounded-full transition" title="Video Call">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                </button>
                <button @click="toggleMinimize" class="p-2 hover:bg-gray-200 rounded-full transition" :title="isMinimized ? 'Restore' : 'Minimize'">
                    <svg :class="isMinimized ? 'rotate-180 transition-transform' : 'transition-transform'" class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                </button>
            </div>
        </div>
        
        
        
        

        <div v-show="!isMinimized" ref="messageContainer" @scroll="checkScrollPosition" class="flex-1 overflow-y-auto bg-gray-50 p-4 space-y-4 z-10">
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
                        
                        <div class="relative">
                            <div class="shadow-sm text-sm relative group overflow-hidden"
                                 :class="[
                                    msg.user_id === currentUser.id ? 'bg-gradient-to-br from-orange-500 to-orange-600 text-white rounded-2xl rounded-br-sm' : 'bg-gradient-to-br from-amber-900 to-stone-800 text-white rounded-2xl rounded-bl-sm',
                                    msg.optimistic ? 'opacity-75' : 'opacity-100'
                                 ]">
                                 
                                 <div v-if="msg.attachment_url" class="p-1 pb-0">
                                    <img :src="msg.attachment_url" class="rounded-lg max-w-full max-h-64 object-cover cursor-pointer hover:opacity-90 transition" @click="window.open(msg.attachment_url, '_blank')">
                                </div>

                                <div v-if="msg.message" class="px-4 py-2 whitespace-pre-wrap leading-relaxed">{{ msg.message }}</div>

                                <div v-if="msg.optimistic" class="absolute bottom-1 right-2">
                                    <svg class="w-3 h-3 text-white animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                </div>
                                
                                <!-- Reaction Button -->
                                <button @click="showReactionPicker = msg.id" :class="['absolute -bottom-1 opacity-0 group-hover:opacity-100 transition-opacity bg-white text-gray-600 rounded-full p-1 shadow-md hover:bg-gray-100', msg.user_id === currentUser.id ? '-right-1' : '-left-1']">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </button>
                            </div>
                            
                            <!-- Reactions Display -->
                            <div v-if="msg.reactions && Object.keys(msg.reactions).length" class="flex gap-1 mt-1">
                                <span v-for="(users, emoji) in msg.reactions" :key="emoji" @click="toggleReaction(msg.id, emoji)" class="inline-flex items-center gap-1 bg-white border border-gray-200 rounded-full px-2 py-0.5 text-xs cursor-pointer hover:bg-gray-50 shadow-sm">
                                    <span>{{ emoji }}</span>
                                    <span class="text-gray-600 font-medium">{{ users.length }}</span>
                                </span>
                            </div>
                            
                            <!-- Reaction Picker -->
                            <div v-if="showReactionPicker === msg.id" :class="['absolute bottom-full mb-2 bg-white rounded-full shadow-lg border border-gray-200 p-2 flex gap-1 z-20', msg.user_id === currentUser.id ? 'right-0' : 'left-0']">
                                <button v-for="emoji in emojiList" :key="emoji" @click="toggleReaction(msg.id, emoji)" class="hover:bg-gray-100 rounded-full p-1 text-lg transition">
                                    {{ emoji }}
                                </button>
                            </div>
                        </div>
                        
                        <span class="text-[10px] text-gray-400 mt-1 px-1">{{ new Date(msg.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div v-show="!isMinimized" class="p-4 bg-white border-t border-gray-200 z-10">
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
                <!-- Microphone -->
                <button type="button" class="h-[48px] w-[40px] flex items-center justify-center text-gray-400 hover:text-gray-600 transition" title="Voice message">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path></svg>
                </button>
                
                <!-- Gallery/Image -->
                <button type="button" @click="openFilePicker" class="h-[48px] w-[40px] flex items-center justify-center text-gray-400 hover:text-gray-600 transition" title="Attach image">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </button>
                
                <input type="file" ref="fileInput" class="hidden" accept="image/*" @change="onFileSelect">

                <div class="flex-1 relative">
                    <textarea v-model="formMessage" @keydown.enter.prevent="submitMessage" @input="sendTyping" rows="1" class="w-full border-gray-300 rounded-2xl px-4 py-3 pr-10 shadow-sm focus:border-orange-500 focus:ring-orange-500 resize-none" placeholder="Aa" style="min-height: 48px; max-height: 120px;"></textarea>
                    
                    <!-- Emoji picker button inside input -->
                    <button type="button" @click="showEmojiPicker = !showEmojiPicker" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </button>
                    
                    <!-- Emoji picker popup -->
                    <div v-if="showEmojiPicker" class="absolute bottom-full right-0 mb-2 bg-white rounded-lg shadow-lg border border-gray-200 p-3 grid grid-cols-8 gap-1 z-20">
                        <button v-for="emoji in emojiList" :key="emoji" @click="addEmoji(emoji)" type="button" class="hover:bg-gray-100 rounded p-1 text-xl transition">
                            {{ emoji }}
                        </button>
                    </div>
                </div>
                
                <!-- Send Button -->
                <button type="submit" class="h-[48px] w-[48px] flex items-center justify-center bg-blue-600 text-white rounded-full hover:bg-blue-700 shadow-md transition disabled:opacity-50" :disabled="!formMessage.trim() && !attachment">
                    <svg class="w-5 h-5 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                </button>
            </form>
        </div>
    </div>
</template>