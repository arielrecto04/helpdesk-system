<script setup>
import { ref, onMounted, nextTick, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    ticketId: Number,
    initialMessages: Array,
    initialMessagesCount: {
        type: Number,
        default: 0,
    },
});

const messages = ref([...props.initialMessages]);
const messageContainer = ref(null);
const currentUser = usePage().props.auth.user;

const formMessage = ref('');

const typingUsers = ref([]);
let typingTimeout = null;
const currentPage = ref(1);
const perPage = ref(20);
const totalMessages = ref(props.initialMessagesCount || messages.value.length || 0);
const loadingMore = ref(false);
const toastMessage = ref('');

const hasMore = () => {
    return messages.value.length < totalMessages.value;
};

const scrollToBottom = () => {
    nextTick(() => {
        if (messageContainer.value) {
            messageContainer.value.scrollTop = messageContainer.value.scrollHeight;
        }
    });
};

const sendTyping = () => {
    try {
        Echo.private(`ticket.${props.ticketId}`).whisper('typing', {
            user_id: currentUser.id,
            name: currentUser.name,
        });
    } catch (e) {
        // ignore if Echo not available
    }
};

const submitMessage = async () => {
    const text = (formMessage.value || '').trim();
    if (!text) return;

    // Optimistic message (temporary id negative to avoid collisions)
    const tempId = `temp-${Date.now()}`;
    const tempMessage = {
        id: tempId,
        ticket_id: props.ticketId,
        user_id: currentUser.id,
        message: text,
        created_at: new Date().toISOString(),
        user: { id: currentUser.id, name: currentUser.name },
        optimistic: true,
    };

    messages.value.push(tempMessage);
    scrollToBottom();
    formMessage.value = '';

    try {
        const res = await axios.post(route('ticket.messages.store', props.ticketId), { message: text });
        const realMessage = res.data;

        // Replace temp message with real message
        const idx = messages.value.findIndex(m => m.id === tempId);
        if (idx !== -1) {
            messages.value.splice(idx, 1, realMessage);
        } else {
            messages.value.push(realMessage);
        }
        scrollToBottom();
    } catch (err) {
        // Remove optimistic message on error and optionally show an error state
        const idx = messages.value.findIndex(m => m.id === tempId);
        if (idx !== -1) messages.value.splice(idx, 1);
        console.error(err);
        showToast('Failed to send message');
    }
};

const showToast = (msg = '') => {
    toastMessage.value = msg;
    setTimeout(() => {
        toastMessage.value = '';
    }, 3500);
};

const loadMore = async () => {
    if (loadingMore.value) return;
    if (!hasMore()) return;
    loadingMore.value = true;
    const nextPage = currentPage.value + 1;
    try {
        const url = route('ticket.messages.index', props.ticketId) + `?page=${nextPage}&per_page=${perPage.value}`;
        const res = await axios.get(url);
        const items = res.data.data || [];
        if (items.length) {
            // Prepend older messages
            messages.value = [...items, ...messages.value];
            currentPage.value = nextPage;
        }
    } catch (e) {
        console.error(e);
        showToast('Failed to load more messages');
    } finally {
        loadingMore.value = false;
    }
};

onMounted(() => {
    scrollToBottom();

    // Listen for new messages (others)
    Echo.private(`ticket.${props.ticketId}`)
        .listen('NewMessageEvent', (e) => {
            messages.value.push(e.message);
            scrollToBottom();
        })
        .listenForWhisper('typing', (e) => {
            if (!e || !e.user_id) return;
            if (e.user_id === currentUser.id) return; // ignore our own typing

            // add or refresh typing user
            const existing = typingUsers.value.find(u => u.user_id === e.user_id);
            if (!existing) {
                typingUsers.value.push({ user_id: e.user_id, name: e.name, _ts: Date.now() });
            } else {
                existing._ts = Date.now();
            }

            // clear after 3s
            setTimeout(() => {
                const now = Date.now();
                typingUsers.value = typingUsers.value.filter(u => (now - u._ts) < 3000);
            }, 3500);
        });
});

// Watch incoming initialMessages prop
watch(() => props.initialMessages, (newVal) => {
    messages.value = newVal;
    scrollToBottom();
});
</script>

<template>
    <div class="bg-white p-4 sm:p-6 rounded-lg shadow border border-gray-200 mt-6">
        <h3 class="text-lg font-medium text-gray-900 mb-1">Conversation</h3>
        <div class="text-sm text-gray-600 mb-3">With: <span class="font-medium">{{ Array.from(new Set(messages.map(m => m.user ? m.user.name : null).filter(Boolean))).filter(n => n !== currentUser.name).join(', ') || 'Support' }}</span></div>
        <div v-if="toastMessage" class="fixed right-6 top-6 z-50">
            <div class="bg-red-600 text-white px-4 py-2 rounded shadow">{{ toastMessage }}</div>
        </div>
        
        <div class="mb-2">
            <button v-if="hasMore()" @click.prevent="loadMore" :disabled="loadingMore" class="text-sm text-gray-600 hover:underline">Load more</button>
        </div>

        <div ref="messageContainer" class="h-96 overflow-y-auto border border-gray-200 rounded-lg bg-gray-50 p-4 mb-4 space-y-4">
            <div v-if="messages.length === 0" class="text-center text-gray-400 text-sm mt-10">
                No messages yet. Start the conversation.
            </div>
            <div v-for="msg in messages" :key="msg.id"
                 class="flex items-start gap-2"
                 :class="msg.user_id === currentUser.id ? 'justify-end' : 'justify-start'">

                <div v-if="msg.user_id !== currentUser.id" class="flex items-center">
                    <div class="w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center text-sm font-semibold text-gray-700 mr-2">
                        {{ (msg.user && msg.user.name) ? msg.user.name.split(' ').map(s=>s[0]).join('').slice(0,2) : 'U' }}
                    </div>
                </div>

                <div class="flex flex-col max-w-[80%]" :class="msg.user_id === currentUser.id ? 'items-end' : 'items-start'">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xs font-bold text-gray-600">
                            {{ msg.user ? msg.user.name : 'User' }}
                        </span>
                        <span class="text-[10px] text-gray-400">
                            {{ new Date(msg.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) }}
                        </span>
                    </div>

                    <div class="py-2 px-4 rounded-2xl text-sm shadow-sm break-words"
                         :class="msg.user_id === currentUser.id 
                            ? 'bg-blue-600 text-white rounded-br-none' 
                            : 'bg-white border border-gray-200 text-gray-800 rounded-bl-none'">
                        <span v-if="msg.optimistic" class="opacity-80">(sending) </span>{{ msg.message }}
                    </div>
                </div>

                <div v-if="msg.user_id === currentUser.id" class="flex items-center">
                    <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-sm font-semibold text-white ml-2">
                        {{ currentUser.name ? currentUser.name.split(' ').map(s=>s[0]).join('').slice(0,2) : 'ME' }}
                    </div>
                </div>
            </div>
        </div>
        <div class="mb-2">
            <div v-if="typingUsers.length" class="text-sm text-gray-500">{{ typingUsers.map(u=>u.name).join(', ') }} is typing&hellip;</div>
        </div>

        <form @submit.prevent="submitMessage" class="flex gap-2">
            <input
                v-model="formMessage"
                @input="() => { sendTyping(); }"
                type="text"
                class="flex-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                placeholder="Type your message here..."
            />
            <button
                type="submit"
                class="px-4 py-2 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 transition disabled:opacity-50"
                :disabled="!formMessage"
            >
                Send
            </button>
        </form>
    </div>
</template>
