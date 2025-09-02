document.addEventListener('DOMContentLoaded', () => {
  // Mobile menu toggle
  const toggle = document.querySelector('.mobile-menu-toggle');
  const menu = document.querySelector('.nav-menu');
  if (toggle && menu) {
    toggle.addEventListener('click', () => {
      menu.style.display = menu.style.display === 'flex' ? 'none' : 'flex';
      if (menu.style.display === 'flex') menu.style.flexDirection = 'column';
    });
  }

  // Newsletter fake submit
  const newsletter = document.getElementById('newsletter-form');
  if (newsletter) {
    newsletter.addEventListener('submit', (e) => {
      e.preventDefault();
      alert('Thanks for subscribing!');
      newsletter.reset();
    });
  }

  // Add to cart buttons
  document.body.addEventListener('click', async (e) => {
    const btn = e.target.closest('.btn-add-to-cart');
    if (!btn) return;
    const productId = btn.getAttribute('data-product-id');
    if (!productId) return;
    
    btn.disabled = true;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    
    try {
      const res = await fetch('add_to_cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ product_id: productId, quantity: 1 })
      });
      const data = await res.json();
      if (data.success) {
        // Update cart UI if present
        const countEl = document.querySelector('.cart-count');
        const totalEl = document.querySelector('.cart-total');
        if (countEl) countEl.textContent = data.cart_count;
        if (totalEl) totalEl.textContent = data.cart_total_formatted;
        
        // Show success feedback
        btn.innerHTML = '<i class="fas fa-check"></i>';
        btn.style.background = '#10b981';
        setTimeout(() => {
          btn.innerHTML = originalText;
          btn.style.background = '';
        }, 1000);
      } else {
        alert(data.message || 'Failed to add to cart');
        btn.innerHTML = originalText;
      }
    } catch (err) {
      alert('Error adding to cart');
      btn.innerHTML = originalText;
    } finally {
      btn.disabled = false;
    }
  });

  // Remove cart item buttons
  document.body.addEventListener('click', async (e) => {
    const btn = e.target.closest('.remove-item');
    if (!btn) return;
    
    if (!confirm('Are you sure you want to remove this item from your cart?')) {
      return;
    }
    
    const cartId = btn.getAttribute('data-cart-id');
    if (!cartId) return;
    
    try {
      const res = await fetch('remove_cart_item.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ cart_id: cartId })
      });
      const data = await res.json();
      if (data.success) {
        // Remove the cart item row
        btn.closest('.cart-item').remove();
        
        // Update cart UI
        const countEl = document.querySelector('.cart-count');
        const totalEl = document.querySelector('.cart-total');
        if (countEl) countEl.textContent = data.cart_count;
        if (totalEl) totalEl.textContent = data.cart_total_formatted;
        
        // If cart is empty, reload page to show empty cart message
        if (data.cart_count === 0) {
          location.reload();
        }
      } else {
        alert(data.message || 'Failed to remove item');
      }
    } catch (err) {
      alert('Error removing item from cart');
    }
  });
});
