import api from './api';

/**
 * Order Service
 * Encapsulates all order-related API calls
 */
const orderService = {
  /**
   * Get order details
   * @param {number|string} orderId
   * @returns {Promise}
   */
  getOrder(orderId) {
    return api.get(`/admin/orders/${orderId}/data`);
  },

  /**
   * Get all orders with filters
   * @param {Object} filters
   * @returns {Promise}
   */
  getOrders(filters = {}) {
    return api.get('/admin/orders-data', { params: filters });
  },

  /**
   * Accept an order (change status to PROCESSING)
   * @param {number|string} orderId
   * @returns {Promise}
   */
  acceptOrder(orderId) {
    return api.put(`/admin/orders/${orderId}/accept`);
  },

  /**
   * Decline an order (change status to CANCELLED)
   * @param {number|string} orderId
   * @returns {Promise}
   */
  declineOrder(orderId) {
    return api.put(`/admin/orders/${orderId}/decline`);
  },

  /**
   * Update order status
   * @param {number|string} orderId
   * @param {string} status
   * @returns {Promise}
   */
  updateStatus(orderId, status) {
    return api.put(`/admin/orders/${orderId}/status`, { status });
  },

  /**
   * Confirm payment for an order
   * @param {number|string} orderId
   * @param {Object} paymentData
   * @returns {Promise}
   */
  confirmPayment(orderId, paymentData) {
    return api.put(`/admin/orders/${orderId}/confirm-payment`, paymentData);
  },

  /**
   * Schedule installation for an order
   * @param {number|string} orderId
   * @param {string} installationDate
   * @returns {Promise}
   */
  scheduleInstallation(orderId, installationDate) {
    return api.put(`/admin/orders/${orderId}/schedule-installation`, {
      installation_date: installationDate,
    });
  },

  /**
   * Download invoice PDF
   * @param {number|string} orderId
   * @returns {Promise}
   */
  downloadInvoice(orderId) {
    return api.get(`/admin/orders/${orderId}/invoice-pdf`, {
      responseType: 'blob',
    });
  },

  /**
   * Add note to order
   * @param {number|string} orderId
   * @param {string} note
   * @returns {Promise}
   */
  addNote(orderId, note) {
    return api.post(`/admin/orders/${orderId}/notes`, { note });
  },

  /**
   * Process refund for an order
   * @param {number|string} orderId
   * @param {Object} refundData
   * @returns {Promise}
   */
  processRefund(orderId, refundData) {
    return api.post(`/admin/orders/${orderId}/refund`, refundData);
  },

  /**
   * Resend notification
   * @param {number|string} orderId
   * @param {string} notificationType
   * @returns {Promise}
   */
  resendNotification(orderId, notificationType) {
    return api.post(`/admin/orders/${orderId}/resend-notification`, {
      notification_type: notificationType,
    });
  },
};

export default orderService;
