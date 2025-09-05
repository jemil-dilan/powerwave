// Notification function for user feedback
function showNotification(message, type = 'info') {
  // Create notification element
  const notification = document.createElement('div');
  notification.className = `notification notification-${type}`;
  notification.style.cssText = `
    position: fixed;
    top: 20px;
    right: 20px;
    background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6'};
    color: white;
    padding: 12px 20px;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    max-width: 350px;
    font-size: 14px;
    opacity: 0;
    transform: translateX(100px);
    transition: all 0.3s ease;
  `;
  notification.textContent = message;
  
  // Add to body
  document.body.appendChild(notification);
  
  // Animate in
  requestAnimationFrame(() => {
    notification.style.opacity = '1';
    notification.style.transform = 'translateX(0)';
  });
  
  // Remove after 4 seconds
  setTimeout(() => {
    notification.style.opacity = '0';
    notification.style.transform = 'translateX(100px)';
    setTimeout(() => {
      if (notification.parentNode) {
        notification.parentNode.removeChild(notification);
      }
    }, 300);
  }, 4000);
}

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
    const btn = e.target.closest('.btn-add-to-cart') || e.target.closest('#add-to-cart-btn');
    if (!btn) return;
    const productId = btn.getAttribute('data-product-id');
    if (!productId) return;
    
    // Get quantity from input if available, otherwise default to 1
    let quantity = 1;
    const quantityInput = document.querySelector('#quantity-input');
    if (quantityInput) {
      quantity = parseInt(quantityInput.value) || 1;
    }
    
    btn.disabled = true;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
    
    try {
      const res = await fetch('add_to_cart.php', {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/x-www-form-urlencoded',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({ product_id: productId, quantity: quantity })
      });
      
      if (!res.ok) {
        throw new Error('Network response was not ok');
      }
      
      const data = await res.json();
      if (data.success) {
        // Update cart UI if present
        const countEl = document.querySelector('.cart-count');
        const totalEl = document.querySelector('.cart-total');
        if (countEl) countEl.textContent = data.cart_count;
        if (totalEl) totalEl.textContent = data.cart_total_formatted;
        
        // Show success feedback
        btn.innerHTML = '<i class="fas fa-check"></i> Added!';
        btn.style.background = '#10b981';
        
        // Show success message
        showNotification(data.message, 'success');
        
        setTimeout(() => {
          btn.innerHTML = originalText;
          btn.style.background = '';
        }, 2000);
      } else {
        showNotification(data.message || 'Failed to add to cart', 'error');
        btn.innerHTML = originalText;
      }
    } catch (err) {
      console.error('Add to cart error:', err);
      showNotification('Error adding to cart. Please try again.', 'error');
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
