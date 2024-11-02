<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
  friend: {
    type: Object,
    required: true
  },
  messages: {
    type: Array,
    default: () => []
  }
})

const page = usePage()
const messagesList = ref(props.messages)
const form = useForm({ content: '' })
let echo = null

onMounted(() => {
  // Single channel for both users
  echo = Echo.private(`chat.${Math.min(props.friend.id, page.props.auth.user.id)}.${Math.max(props.friend.id, page.props.auth.user.id)}`)
    .listen('MessageSent', (e) => {
      messagesList.value.push(e.message)
      scrollToBottom()
    })

  scrollToBottom()
})

onUnmounted(() => {
  // Cleanup listeners
  if (echo) {
    echo.stopListening('MessageSent')
  }
  Echo.leave(`chat.${Math.min(props.friend.id, page.props.auth.user.id)}.${Math.max(props.friend.id, page.props.auth.user.id)}`)
})

function scrollToBottom() {
  nextTick(() => {
    const container = document.querySelector('.messages-container')
    if (container) {
      container.scrollTop = container.scrollHeight
    }
  })
}

function sendMessage() {
  if (!form.content.trim()) return

  form.post(route('messages.store', props.friend.id), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      form.reset('content')
      scrollToBottom()
    }
  })
}
</script>

<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Chat with {{ friend.name }}
      </h2>
    </template>
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
      <div class="bg-white rounded-lg shadow">
        <!-- Messages Container -->
        <div class="messages-container h-[500px] overflow-y-auto p-4 space-y-4">
          <div v-if="messagesList.length === 0" class="text-center text-gray-500">
            No messages yet. Start a conversation!
          </div>
          
          <div
            v-for="message in messagesList"
            :key="message.id"
            :class="[
              'p-3 rounded-lg max-w-[70%]',
              message.sender_id === page.props.auth.user.id
                ? 'ml-auto bg-blue-500 text-white'
                : 'bg-gray-100'
            ]"
          >
            <div class="text-sm">{{ message.content }}</div>
            <div class="text-xs mt-1 opacity-75">
              {{ new Date(message.created_at).toLocaleTimeString() }}
            </div>
          </div>
        </div>

        <!-- Message Input -->
        <div class="border-t p-4">
          <form @submit.prevent="sendMessage" class="flex gap-2">
            <input
              v-model="form.content"
              type="text"
              class="flex-1 rounded-lg border-gray-300"
              placeholder="Type your message..."
              :disabled="form.processing"
            >
            <button
              type="submit"
              class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 disabled:opacity-50"
              :disabled="form.processing || !form.content.trim()"
            >
              {{ form.processing ? 'Sending...' : 'Send' }}
            </button>
          </form>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
