import dayjs from 'dayjs'
import 'dayjs/locale/id'
import 'dayjs/locale/en'
import { useI18n } from 'vue-i18n' // Kita akan akses locale aktif

const helpers = {
  formatDate(value, format = 'DD MMM YYYY HH:mm', locale = 'id') {
    if (!value) return '-'
    dayjs.locale(locale)
    return dayjs(value).format(format)
  },
  formatCurrency(value, locale = 'id-ID', currency = 'IDR') {
    if (value == null) return '-'
    return new Intl.NumberFormat(locale, {
      style: 'currency',
      currency
    }).format(value)
  },
  formatNumber(value, locale = 'id-ID') {
    if (!value && value !== 0) return '-'
    return new Intl.NumberFormat(locale, {
      notation: 'compact',
      compactDisplay: 'short',
      maximumFractionDigits: 1,
    }).format(value)
  },
  capitalize(text) {
    if (!text) return ''
    return text.charAt(0).toUpperCase() + text.slice(1)
  }
}

// Plugin
export default {
  install(app) {
    app.config.globalProperties.$helpers = {
      formatDate: (value, format) => {
        const { locale } = useI18n(); // Memanggil useI18n setiap kali fungsi dipanggil
        return helpers.formatDate(value, format, locale.value);
      },
      formatCurrency: (value) => {
        const { locale } = useI18n();
        return helpers.formatCurrency(value, locale.value === 'en' ? 'en-US' : 'id-ID', locale.value === 'en' ? 'USD' : 'IDR');
      },
      formatNumber: (value) => {
        const { locale } = useI18n();
        return helpers.formatNumber(value, locale.value === 'en' ? 'en-US' : 'id-ID');
      },
      capitalize: helpers.capitalize
    };
    
  }
}