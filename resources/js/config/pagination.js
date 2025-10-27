/**
 * Centralized pagination configuration
 * Used across both frontend and backend for consistency
 */

export const PAGINATION = {
  // Default pagination limits
  DEFAULT: 15,
  
  // Specific limits for different resources
  ORDERS: 15,
  PRODUCTS: 20,
  USERS: 25,
  WARRANTIES: 15,
  INSTALLMENTS: 15,
  QUOTES: 15,
  
  // Dashboard limits
  DASHBOARD_RECENT: 5,
  DASHBOARD_ITEMS: 4,
  RECENT_ACTIVITY: 10,
  
  // Search results
  SEARCH_RESULTS: 20,
  
  // Admin pagination (usually higher for overview pages)
  ADMIN_OVERVIEW: 25,
  
  // Analytics
  ANALYTICS_LIMIT: 50,
}

/**
 * Get pagination limit for a specific resource
 * @param {string} resource - Resource name (e.g., 'orders', 'products')
 * @returns {number} Pagination limit
 */
export const getPaginationLimit = (resource) => {
  const key = resource.toUpperCase()
  return PAGINATION[key] || PAGINATION.DEFAULT
}

/**
 * Common pagination options for select dropdowns
 */
export const PER_PAGE_OPTIONS = [5, 10, 15, 20, 25, 50, 100]

/**
 * Generate pagination meta for API responses
 * @param {object} paginator - Laravel paginator object
 * @returns {object} Standardized pagination meta
 */
export const getPaginationMeta = (paginator) => {
  return {
    current_page: paginator.current_page || 1,
    last_page: paginator.last_page || 1,
    per_page: paginator.per_page || PAGINATION.DEFAULT,
    total: paginator.total || 0,
    from: paginator.from || 0,
    to: paginator.to || 0,
    has_more_pages: paginator.has_more_pages || false,
  }
}

