import { ref, computed } from 'vue'

export interface Notification {
  id: string
  type: 'success' | 'error' | 'warning' | 'info'
  message: string
  duration?: number
}

export function useNotification() {
  const notifications = ref<Notification[]>([])

  const show = (notification: Omit<Notification, 'id'>) => {
    const id = Math.random().toString(36).substr(2, 9)
    const item: Notification = {
      ...notification,
      id,
      duration: notification.duration || 3000
    }

    notifications.value.push(item)

    if (item.duration) {
      setTimeout(() => {
        remove(id)
      }, item.duration)
    }

    return id
  }

  const remove = (id: string) => {
    notifications.value = notifications.value.filter(n => n.id !== id)
  }

  const success = (message: string, duration?: number) => {
    return show({ type: 'success', message, duration })
  }

  const error = (message: string, duration?: number) => {
    return show({ type: 'error', message, duration })
  }

  const warning = (message: string, duration?: number) => {
    return show({ type: 'warning', message, duration })
  }

  const info = (message: string, duration?: number) => {
    return show({ type: 'info', message, duration })
  }

  const clear = () => {
    notifications.value = []
  }

  return {
    notifications: computed(() => notifications.value),
    show,
    remove,
    success,
    error,
    warning,
    info,
    clear
  }
}
