<script setup>
defineProps({
  invoice: { type: Object, required: true },
  app:     { type: Object, required: true },
})
</script>

<template>
  <div class="pf-page">
    <div class="pf-card">
      <!-- Animated checkmark -->
      <div class="icon-wrap icon-success">
        <svg class="check-ring" viewBox="0 0 50 50">
          <circle cx="25" cy="25" r="22" fill="none" stroke="#22c55e" stroke-width="2"
                  stroke-dasharray="138" stroke-dashoffset="138"
                  style="animation: drawRing 0.6s 0.1s ease forwards;"/>
        </svg>
        <svg class="icon-inner" fill="none" stroke="currentColor" viewBox="0 0 24 24"
             style="animation: popIn 0.3s 0.65s ease both; opacity: 0;">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M5 13l4 4L19 7"/>
        </svg>
      </div>

      <h1 class="pf-title">Payment Confirmed</h1>
      <p class="pf-body">
        Thank you, <strong>{{ invoice.customer_name }}</strong>. Your payment for
        <strong>{{ invoice.reference }}</strong> has been successfully processed.
        A receipt has been sent to your email.
      </p>

      <div class="pf-detail-card">
        <div class="pf-row">
          <span class="pf-label">Invoice</span>
          <span class="pf-value">{{ invoice.reference }}</span>
        </div>
        <div class="pf-row">
          <span class="pf-label">Billed to</span>
          <span class="pf-value">{{ invoice.customer_name }}</span>
        </div>
        <div class="pf-row">
          <span class="pf-label">Amount paid</span>
          <span class="pf-value pf-amount-success">R {{ Number(invoice.paid_total).toLocaleString('en-ZA', { minimumFractionDigits: 2 }) }}</span>
        </div>
        <div class="pf-row pf-row-last">
          <span class="pf-label">Status</span>
          <span class="pf-badge-success">Paid</span>
        </div>
      </div>

      <p class="pf-hint">A receipt has been emailed to {{ invoice.customer_email }}.</p>
      <p class="pf-brand">{{ app.name }}</p>
    </div>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap');

.pf-page {
  min-height: 100vh;
  background: #f7f7f5;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1.5rem;
  font-family: 'DM Sans', sans-serif;
}

.pf-card {
  background: #fff;
  border-radius: 20px;
  border: 1px solid #e8e8e4;
  padding: 3rem 2.5rem;
  max-width: 420px;
  width: 100%;
  text-align: center;
  box-shadow: 0 4px 24px rgba(0,0,0,0.06);
  animation: fadeUp 0.5s ease both;
}

@keyframes fadeUp {
  from { opacity: 0; transform: translateY(16px); }
  to   { opacity: 1; transform: translateY(0); }
}

.icon-wrap {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1.75rem;
  position: relative;
}

.icon-success { background: #f0fdf4; }

.check-ring {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
}

@keyframes drawRing {
  to { stroke-dashoffset: 0; }
}

@keyframes popIn {
  from { opacity: 0; transform: scale(0.5); }
  to   { opacity: 1; transform: scale(1); }
}

.icon-inner { width: 30px; height: 30px; color: #16a34a; position: relative; z-index: 1; }

.pf-title {
  font-size: 1.35rem;
  font-weight: 600;
  color: #1a1a18;
  margin: 0 0 0.6rem;
  letter-spacing: -0.02em;
}

.pf-body {
  font-size: 0.9rem;
  color: #6b6b65;
  line-height: 1.6;
  margin: 0 0 1.75rem;
}

.pf-body strong { color: #1a1a18; font-weight: 500; }

.pf-detail-card {
  background: #f7f7f5;
  border-radius: 12px;
  padding: 0.25rem 1.25rem;
  margin-bottom: 1.5rem;
  text-align: left;
}

.pf-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.7rem 0;
  border-bottom: 1px solid #ececea;
  font-size: 0.875rem;
}
.pf-row-last { border-bottom: none; }

.pf-label { color: #9b9b93; }
.pf-value { font-weight: 500; color: #1a1a18; font-family: 'DM Mono', monospace; font-size: 0.82rem; }

.pf-amount-success { color: #16a34a; font-size: 0.9rem; font-weight: 600; font-family: 'DM Mono', monospace; }

.pf-badge-success {
  background: #dcfce7;
  color: #15803d;
  font-size: 0.72rem;
  font-weight: 600;
  padding: 0.2rem 0.65rem;
  border-radius: 99px;
  letter-spacing: 0.04em;
  text-transform: uppercase;
}

.pf-hint {
  font-size: 0.8rem;
  color: #b0b0a8;
  margin: 0 0 1.5rem;
  line-height: 1.5;
}

.pf-brand {
  font-size: 0.75rem;
  color: #c8c8c0;
  margin: 0;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  font-weight: 500;
}
</style>
