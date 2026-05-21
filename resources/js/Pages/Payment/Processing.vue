<script setup>
defineProps({
  invoice: { type: Object, required: true },
  app:     { type: Object, required: true },
})
</script>

<template>
  <div class="pf-page">
    <div class="pf-card">
      <!-- Animated spinner ring -->
      <div class="icon-wrap icon-processing">
        <svg class="spinner" viewBox="0 0 50 50">
          <circle class="spinner-track" cx="25" cy="25" r="20" fill="none" stroke-width="3"/>
          <circle class="spinner-arc"   cx="25" cy="25" r="20" fill="none" stroke-width="3"
                  stroke-dasharray="80 200" stroke-linecap="round"/>
        </svg>
        <svg class="icon-inner" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
      </div>

      <h1 class="pf-title">Payment Processing</h1>
      <p class="pf-body">
        Your payment for <strong>{{ invoice.reference }}</strong> has been received
        and is currently being verified. This usually takes just a moment.
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
        <div class="pf-row pf-row-last">
          <span class="pf-label">Amount</span>
          <span class="pf-value pf-amount">R {{ Number(invoice.total).toLocaleString('en-ZA', { minimumFractionDigits: 2 }) }}</span>
        </div>
      </div>

      <p class="pf-hint">A receipt will be emailed to you once confirmation is complete.</p>
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

.icon-processing { background: #fdf8ee; }

.spinner {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  animation: rotate 1.8s linear infinite;
}
.spinner-track { stroke: #f0e9d4; }
.spinner-arc   { stroke: #d4a017; animation: dash 1.5s ease-in-out infinite; }

@keyframes rotate { to { transform: rotate(360deg); } }
@keyframes dash {
  0%   { stroke-dasharray: 1 200; stroke-dashoffset: 0; }
  50%  { stroke-dasharray: 89 200; stroke-dashoffset: -35; }
  100% { stroke-dasharray: 89 200; stroke-dashoffset: -124; }
}

.icon-inner { width: 28px; height: 28px; color: #c49a14; position: relative; z-index: 1; }

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
.pf-amount { color: #c49a14; font-size: 0.9rem; }

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
