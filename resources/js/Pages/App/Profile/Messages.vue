<script setup>
import { ref, nextTick, watch, onMounted } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3'; // Pakai useForm untuk kirim pesan
import AppLayout from '@/Layouts/AppLayout.vue';

import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import Avatar from 'primevue/avatar';
import Textarea from 'primevue/textarea';

const props = defineProps({
    contacts: Array,    // Daftar Semua User
    activeChat: Array,  // Isi Chat
    activeUser: Object, // User yang sedang dichat
    currentUser: Object
});

const form = useForm({
    receiver_id: props.activeUser?.id,
    body: '',
});

const chatContainer = ref(null);

// Scroll ke bawah otomatis
const scrollToBottom = () => {
    nextTick(() => {
        if (chatContainer.value) {
            chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
        }
    });
};

watch(() => props.activeChat, scrollToBottom);
onMounted(scrollToBottom);

// Pilih Kontak (Pindah URL)
const selectContact = (userId) => {
    router.get(route('profile.messages'), { user_id: userId }, { preserveState: true });
};

// Kirim Pesan Real (Simpan ke DB)
const sendMessage = () => {
    if (!form.body.trim() || !props.activeUser) return;
    
    form.receiver_id = props.activeUser.id; // Pastikan ID penerima benar

    // Kita pakai router.post manual atau buat route khusus kirim pesan
    // Disini saya asumsikan kita buat route baru 'messages.send'
    router.post(route('messages.store'), form, {
        onSuccess: () => {
            form.reset('body');
            scrollToBottom();
        },
        preserveScroll: true
    });
};

const formatTime = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
};
</script>

<template>
    <Head title="Pesan & Komunikasi" />
    <AppLayout>
        <div class="max-w-6xl mx-auto h-[calc(100vh-140px)] flex flex-col">
            
            <div class="mb-4 flex-shrink-0">
                <h1 class="text-2xl font-black text-gray-800 tracking-tight">Communication Hub</h1>
            </div>

            <div class="flex-1 bg-white rounded-2xl border border-gray-200 shadow-xl overflow-hidden flex min-h-0">
                
                <div class="w-full md:w-80 border-r border-gray-100 bg-gray-50 flex flex-col">
                    <div class="p-4 border-b border-gray-100 bg-white">
                        <span class="p-input-icon-left w-full">
                            <i class="pi pi-search text-gray-400" />
                            <InputText placeholder="Cari kontak..." class="w-full p-inputtext-sm rounded-lg bg-gray-50 border-none" />
                        </span>
                    </div>

                    <div class="flex-1 overflow-y-auto custom-scrollbar p-2 space-y-1">
                        <div v-for="contact in contacts" :key="contact.id" 
                             @click="selectContact(contact.id)"
                             class="p-3 rounded-xl cursor-pointer transition-all flex items-center gap-3 group relative"
                             :class="activeUser?.id === contact.id ? 'bg-white shadow-md border border-gray-100' : 'hover:bg-white hover:shadow-sm'">
                            
                            <div class="relative">
                                <Avatar :image="contact.photo ? '/storage/' + contact.photo : null" 
                                        :label="!contact.photo ? contact.name[0] : null" 
                                        shape="circle" 
                                        class="!bg-gradient-to-br !from-emerald-400 !to-teal-600 !text-white !font-bold shadow-md object-cover" size="large" />
                                <span v-if="contact.unread > 0" class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 border-2 border-white rounded-full flex items-center justify-center text-[10px] text-white font-bold">
                                    {{ contact.unread }}
                                </span>
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-baseline">
                                    <h4 class="text-sm font-bold text-gray-800 truncate">{{ contact.name }}</h4>
                                    <span class="text-[10px] text-gray-400 font-medium">{{ formatTime(contact.last_time) }}</span>
                                </div>
                                <p class="text-xs text-gray-500 truncate mt-0.5 group-hover:text-emerald-600 transition-colors">
                                    {{ contact.last_message || 'Mulai percakapan baru' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex-1 flex flex-col bg-white relative">
                    
                    <div v-if="activeUser" class="h-16 border-b border-gray-100 flex items-center justify-between px-6 bg-white/80 backdrop-blur-md absolute top-0 w-full z-10">
                        <div class="flex items-center gap-3">
                            <Avatar :label="activeUser.name[0]" shape="circle" class="!bg-emerald-100 !text-emerald-700 font-bold" />
                            <div>
                                <h3 class="text-sm font-bold text-gray-800">{{ activeUser.name }}</h3>
                                <div class="flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span>
                                    <span class="text-[10px] text-gray-400">Online</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="activeUser" ref="chatContainer" class="flex-1 overflow-y-auto p-6 space-y-6 pt-20 pb-4 bg-[#f8fafc]">
                        <div v-for="msg in activeChat" :key="msg.id" 
                             class="flex flex-col gap-1"
                             :class="msg.sender_id === currentUser.id ? 'items-end' : 'items-start'">
                            
                            <div class="max-w-[70%] px-4 py-3 rounded-2xl shadow-sm text-sm leading-relaxed relative group"
                                 :class="msg.sender_id === currentUser.id 
                                    ? 'bg-emerald-600 text-white rounded-tr-none' 
                                    : 'bg-white text-gray-700 border border-gray-100 rounded-tl-none'">
                                {{ msg.body }}
                                <span class="absolute -bottom-5 text-[10px] text-gray-400 opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap"
                                      :class="msg.sender_id === currentUser.id ? 'right-0' : 'left-0'">
                                    {{ formatTime(msg.created_at) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div v-if="activeUser" class="p-4 bg-white border-t border-gray-100">
                        <div class="flex items-end gap-2 bg-gray-50 p-2 rounded-xl border border-gray-200 focus-within:border-emerald-400 focus-within:ring-2 focus-within:ring-emerald-100 transition-all">
                            <Button icon="pi pi-paperclip" text rounded severity="secondary" class="!w-8 !h-8 !text-gray-400" />
                            <Textarea v-model="form.body" autoResize rows="1" 
                                      placeholder="Ketik pesan..." 
                                      class="flex-1 !bg-transparent !border-none !shadow-none !text-sm py-2 max-h-32 focus:ring-0" 
                                      @keydown.enter.prevent="sendMessage" />
                            <Button icon="pi pi-send" rounded class="!w-8 !h-8 !bg-emerald-600 !border-emerald-600 !text-xs" 
                                    @click="sendMessage" :loading="form.processing" />
                        </div>
                    </div>

                    <div v-else class="flex-1 flex flex-col items-center justify-center bg-gray-50/50">
                        <div class="w-32 h-32 bg-emerald-50 rounded-full flex items-center justify-center mb-6 animate-pulse">
                            <i class="pi pi-comments text-5xl text-emerald-200"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800">Mulai Percakapan</h3>
                        <p class="text-gray-400 text-sm mt-2 max-w-xs text-center">
                            Pilih rekan kerja di sebelah kiri untuk mulai chatting.
                        </p>
                    </div>

                </div>
            </div>
        </div>
    </AppLayout>
</template>