/**
 * bootstrap.js
 * ─────────────
 * Axios HTTP client setup করা হচ্ছে।
 * Laravel-এর CSRF protection-এর জন্য X-XSRF-TOKEN header
 * প্রতিটি request-এ automatically যোগ হবে।
 */
import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';