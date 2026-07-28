<template>
  <i :class="iconClass"></i>
</template>

<script>
export default {
  props: {
    name: String,
  },
  computed: {
    iconClass() {
      const iconMap = {
        'chevron-down': 'ri-arrow-down-s-line',
        'chevron-right': 'ri-arrow-right-s-line',
        'chevron-left': 'ri-arrow-left-s-line',
        'dashboard': 'ri-dashboard-line',
        'office': 'ri-building-line',
        'printer': 'ri-printer-line',
        'trash': 'ri-delete-bin-line',
        'users': 'ri-group-line',
        'user': 'ri-user-line',
        'key': 'ri-key-line',
        'logout': 'ri-logout-box-line',
        'menu': 'ri-menu-line',
      };
      return iconMap[this.name] || 'ri-question-line';
    }
  }
}
</script>
