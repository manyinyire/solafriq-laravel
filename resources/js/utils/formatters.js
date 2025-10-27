/**
 * Centralized formatting utilities for the SolaFriq application
 * All formatting functions should be imported from this file
 */

/**
 * Format currency value
 * @param {number} value - The numeric value to format
 * @param {string} currency - The currency code (default: USD)
 * @returns {string} Formatted currency string (e.g., "$1,234.56")
 */
export const formatCurrency = (value, currency = 'USD') => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency,
  }).format(value || 0)
}

/**
 * Format date string
 * @param {string|Date} dateString - The date to format
 * @param {string} format - 'short' or 'medium' format
 * @returns {string} Formatted date string
 */
export const formatDate = (dateString, format = 'short') => {
  if (!dateString) return ''
  
  const date = typeof dateString === 'string' ? new Date(dateString) : dateString
  const options = {
    short: { year: 'numeric', month: 'short', day: 'numeric' },
    medium: { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' },
    long: { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' },
  }

  return new Intl.DateTimeFormat('en-US', options[format] || options.short).format(date)
}

/**
 * Format percentage value
 * @param {number} value - The numeric value to format
 * @returns {string} Formatted percentage string (e.g., "+5.2%")
 */
export const formatPercentage = (value) => {
  return `${value > 0 ? '+' : ''}${value.toFixed(1)}%`
}

/**
 * Get status badge color classes
 * @param {string} status - The status string
 * @param {string} type - The type of status (order, payment, warranty, etc.)
 * @returns {string} Tailwind CSS classes for status badge
 */
export const getStatusColor = (status, type = 'order') => {
  const statusColors = {
    order: {
      PENDING: 'bg-yellow-100 text-yellow-800',
      PROCESSING: 'bg-blue-100 text-blue-800',
      SHIPPED: 'bg-indigo-100 text-indigo-800',
      DELIVERED: 'bg-green-100 text-green-800',
      COMPLETED: 'bg-green-100 text-green-800',
      CANCELLED: 'bg-red-100 text-red-800',
      RETURNED: 'bg-gray-100 text-gray-800',
      INSTALLED: 'bg-green-100 text-green-800',
      SCHEDULED: 'bg-purple-100 text-purple-800',
      ACCEPTED: 'bg-blue-100 text-blue-800',
    },
    payment: {
      PENDING: 'bg-orange-100 text-orange-800',
      PAID: 'bg-green-100 text-green-800',
      FAILED: 'bg-red-100 text-red-800',
      REFUNDED: 'bg-gray-100 text-gray-800',
      OVERDUE: 'bg-red-100 text-red-800',
    },
    warranty: {
      ACTIVE: 'bg-green-100 text-green-800',
      EXPIRED: 'bg-red-100 text-red-800',
      PENDING: 'bg-yellow-100 text-yellow-800',
    },
    claim: {
      PENDING: 'bg-yellow-100 text-yellow-800',
      APPROVED: 'bg-green-100 text-green-800',
      REJECTED: 'bg-red-100 text-red-800',
      PROCESSING: 'bg-blue-100 text-blue-800',
    },
  }

  return statusColors[type]?.[status] || 'bg-gray-100 text-gray-800'
}

/**
 * Get growth indicator color
 * @param {number} growth - The growth percentage
 * @returns {string} Tailwind CSS color class
 */
export const getGrowthColor = (growth) => {
  if (growth > 0) return 'text-green-600'
  if (growth < 0) return 'text-red-600'
  return 'text-gray-500'
}

/**
 * Format file size
 * @param {number} bytes - File size in bytes
 * @returns {string} Human-readable file size
 */
export const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i]
}

/**
 * Truncate text to specified length
 * @param {string} text - The text to truncate
 * @param {number} maxLength - Maximum length
 * @returns {string} Truncated text with ellipsis
 */
export const truncate = (text, maxLength = 50) => {
  if (!text || text.length <= maxLength) return text
  return text.substring(0, maxLength) + '...'
}

/**
 * Format phone number
 * @param {string} phone - The phone number
 * @returns {string} Formatted phone number
 */
export const formatPhone = (phone) => {
  if (!phone) return ''
  // Remove all non-digit characters
  const cleaned = phone.replace(/\D/g, '')
  // Format as (XXX) XXX-XXXX
  if (cleaned.length === 10) {
    return `(${cleaned.slice(0, 3)}) ${cleaned.slice(3, 6)}-${cleaned.slice(6)}`
  }
  return phone
}

/**
 * Generate initials from a name
 * @param {string} name - Full name
 * @returns {string} Initials (e.g., "JD" from "John Doe")
 */
export const getInitials = (name) => {
  if (!name) return ''
  const parts = name.trim().split(' ')
  if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase()
  return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
}

/**
 * Format relative time (e.g., "2 hours ago")
 * @param {string|Date} date - The date
 * @returns {string} Relative time string
 */
export const formatRelativeTime = (date) => {
  if (!date) return ''
  
  const now = new Date()
  const then = typeof date === 'string' ? new Date(date) : date
  const diff = Math.floor((now - then) / 1000) // Difference in seconds

  const intervals = [
    { name: 'year', seconds: 31536000 },
    { name: 'month', seconds: 2592000 },
    { name: 'week', seconds: 604800 },
    { name: 'day', seconds: 86400 },
    { name: 'hour', seconds: 3600 },
    { name: 'minute', seconds: 60 },
    { name: 'second', seconds: 1 },
  ]

  for (const interval of intervals) {
    const count = Math.floor(diff / interval.seconds)
    if (count >= 1) {
      return `${count} ${interval.name}${count > 1 ? 's' : ''} ago`
    }
  }

  return 'just now'
}

