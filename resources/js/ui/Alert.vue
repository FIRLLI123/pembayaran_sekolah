<template>
  <div v-if="show" class="alert-wrapper">
    <div class="alert-box" :class="type">
      
      <!-- ICON -->
      <div class="alert-icon">
        <svg v-if="type === 'success'" viewBox="0 0 24 24">
          <path d="M9 16.2l-3.5-3.5L4 14.2l5 5 12-12-1.5-1.5z"/>
        </svg>

        <svg v-if="type === 'error'" viewBox="0 0 24 24">
          <path d="M12 10.6L7.4 6 6 7.4 10.6 12 6 16.6 7.4 18 12 13.4 16.6 18 18 16.6 13.4 12 18 7.4 16.6 6z"/>
        </svg>
      </div>

      <!-- TEXT -->
      <div class="alert-content">
        <p>{{ message }}</p>
      </div>

    </div>
  </div>
</template>

<script>
export default {
  props: {
    show: Boolean,
    type: String, // success | error
    message: String
  }
}
</script>

<style scoped>
.alert-wrapper {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 9999;
}

.alert-box {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 16px;
  border-radius: 12px;
  color: white;
  min-width: 250px;
  box-shadow: 0 10px 20px rgba(0,0,0,0.2);
  animation: slideIn 0.3s ease;
}

/* SUCCESS */
.alert-box.success {
  background: #28a745;
}

/* ERROR */
.alert-box.error {
  background: #dc3545;
}

.alert-icon svg {
  width: 20px;
  height: 20px;
  fill: white;
}

@keyframes slideIn {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}
</style>