/**
 * ═══════════════════════════════════════════════════════════════
 * CUSTOM DROPDOWN BEHAVIOR - Gestion améliorée du menu utilisateur
 * ═══════════════════════════════════════════════════════════════
 */

(function() {
  'use strict';

  // Attendre que le DOM soit chargé
  document.addEventListener('DOMContentLoaded', function() {
    
    // ─────────────────────────────────────────────────────────────
    // 1. GESTION DU DROPDOWN UTILISATEUR
    // ─────────────────────────────────────────────────────────────
    
    const userDropdown = document.querySelector('.navbar-dropdown.dropdown-user');
    
    if (userDropdown) {
      let closeTimeout;
      const dropdownToggle = userDropdown.querySelector('[data-bs-toggle="dropdown"]');
      const dropdownMenu = userDropdown.querySelector('.dropdown-menu');
      
      // Empêcher la fermeture immédiate
      if (dropdownToggle && dropdownMenu) {
        
        // Ouvrir le dropdown au clic
        dropdownToggle.addEventListener('click', function(e) {
          e.preventDefault();
          e.stopPropagation();
          
          // Annuler tout timeout de fermeture en cours
          if (closeTimeout) {
            clearTimeout(closeTimeout);
          }
          
          // Toggle le dropdown
          const isShown = userDropdown.classList.contains('show');
          
          if (isShown) {
            closeDropdown();
          } else {
            openDropdown();
          }
        });
        
        // Garder ouvert quand la souris est sur le menu
        dropdownMenu.addEventListener('mouseenter', function() {
          if (closeTimeout) {
            clearTimeout(closeTimeout);
          }
        });
        
        // Délai avant fermeture quand la souris quitte
        dropdownMenu.addEventListener('mouseleave', function() {
          closeTimeout = setTimeout(function() {
            closeDropdown();
          }, 2000); // 2000ms de délai (2 secondes)
        });
        
        // Délai avant fermeture quand la souris quitte le toggle
        dropdownToggle.addEventListener('mouseleave', function() {
          if (userDropdown.classList.contains('show')) {
            closeTimeout = setTimeout(function() {
              closeDropdown();
            }, 1500); // 1500ms de délai (1.5 secondes)
          }
        });
        
        // Annuler la fermeture si on revient sur le toggle
        dropdownToggle.addEventListener('mouseenter', function() {
          if (closeTimeout) {
            clearTimeout(closeTimeout);
          }
        });
        
        // Fermer au clic en dehors
        document.addEventListener('click', function(e) {
          if (!userDropdown.contains(e.target)) {
            closeDropdown();
          }
        });
        
        // Empêcher la propagation des clics dans le menu
        dropdownMenu.addEventListener('click', function(e) {
          // Permettre la navigation sur les liens
          if (e.target.tagName === 'A' || e.target.closest('a')) {
            // Laisser le lien fonctionner normalement
            return;
          }
          e.stopPropagation();
        });
      }
      
      function openDropdown() {
        userDropdown.classList.add('show');
        dropdownMenu.classList.add('show');
        dropdownToggle.setAttribute('aria-expanded', 'true');
      }
      
      function closeDropdown() {
        userDropdown.classList.remove('show');
        dropdownMenu.classList.remove('show');
        dropdownToggle.setAttribute('aria-expanded', 'false');
        if (closeTimeout) {
          clearTimeout(closeTimeout);
        }
      }
    }
    
    // ─────────────────────────────────────────────────────────────
    // 2. GESTION DES NOTIFICATIONS
    // ─────────────────────────────────────────────────────────────
    
    // Fonction pour fermer une notification
    window.dismissNotif = function(btn, event) {
      event.preventDefault();
      event.stopPropagation();
      
      const notifItem = btn.closest('.list-group-item');
      if (notifItem) {
        notifItem.style.transition = 'all 0.3s ease';
        notifItem.style.opacity = '0';
        notifItem.style.transform = 'translateX(20px)';
        
        setTimeout(function() {
          notifItem.remove();
          updateNotifCount();
        }, 300);
      }
    };
    
    // Fonction pour marquer toutes les notifications comme lues
    window.markAllRead = function() {
      const notifList = document.querySelector('.notif-list');
      if (notifList) {
        const items = notifList.querySelectorAll('.list-group-item');
        items.forEach(function(item, index) {
          setTimeout(function() {
            item.style.transition = 'all 0.3s ease';
            item.style.opacity = '0';
            item.style.transform = 'translateX(20px)';
          }, index * 50);
        });
        
        setTimeout(function() {
          notifList.innerHTML = '<div class="text-center py-4 text-body-secondary"><i class="bx bx-check-circle bx-lg mb-2"></i><p class="mb-0">Aucune notification</p></div>';
          updateNotifCount();
        }, items.length * 50 + 300);
      }
    };
    
    // Mettre à jour le compteur de notifications
    function updateNotifCount() {
      const notifList = document.querySelector('.notif-list');
      if (notifList) {
        const count = notifList.querySelectorAll('.list-group-item').length;
        const badge = document.getElementById('notif-badge');
        const badgeIcon = document.getElementById('notif-count');
        
        if (badge) {
          badge.textContent = count > 0 ? count + ' nouvelle' + (count > 1 ? 's' : '') : 'Aucune';
        }
        
        if (badgeIcon) {
          if (count === 0) {
            badgeIcon.style.display = 'none';
          } else {
            badgeIcon.style.display = 'block';
            badgeIcon.textContent = count;
          }
        }
      }
    }
    
    // ─────────────────────────────────────────────────────────────
    // 3. AMÉLIORATION DE LA NAVIGATION
    // ─────────────────────────────────────────────────────────────
    
    // Animation du logo au survol
    const logo = document.querySelector('.app-brand-logo');
    if (logo) {
      logo.style.cursor = 'pointer';
    }
    
    // Smooth scroll pour les ancres
    document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
      anchor.addEventListener('click', function(e) {
        const href = this.getAttribute('href');
        if (href !== '#' && href !== 'javascript:void(0);') {
          e.preventDefault();
          const target = document.querySelector(href);
          if (target) {
            target.scrollIntoView({
              behavior: 'smooth',
              block: 'start'
            });
          }
        }
      });
    });
    
    // ─────────────────────────────────────────────────────────────
    // 4. ACCESSIBILITÉ - Navigation au clavier
    // ─────────────────────────────────────────────────────────────
    
    const dropdownItems = document.querySelectorAll('.dropdown-menu .dropdown-item');
    dropdownItems.forEach(function(item, index) {
      item.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowDown') {
          e.preventDefault();
          const next = dropdownItems[index + 1];
          if (next) next.focus();
        } else if (e.key === 'ArrowUp') {
          e.preventDefault();
          const prev = dropdownItems[index - 1];
          if (prev) prev.focus();
        } else if (e.key === 'Escape') {
          const dropdown = item.closest('.dropdown');
          if (dropdown) {
            const toggle = dropdown.querySelector('[data-bs-toggle="dropdown"]');
            if (toggle) {
              toggle.click();
              toggle.focus();
            }
          }
        }
      });
    });
    
    // ─────────────────────────────────────────────────────────────
    // 5. FEEDBACK VISUEL
    // ─────────────────────────────────────────────────────────────
    
    // Ajouter un effet de ripple sur les boutons
    const buttons = document.querySelectorAll('.btn, .dropdown-item');
    buttons.forEach(function(button) {
      button.addEventListener('click', function(e) {
        const ripple = document.createElement('span');
        const rect = button.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = e.clientX - rect.left - size / 2;
        const y = e.clientY - rect.top - size / 2;
        
        ripple.style.width = ripple.style.height = size + 'px';
        ripple.style.left = x + 'px';
        ripple.style.top = y + 'px';
        ripple.classList.add('ripple-effect');
        
        button.style.position = 'relative';
        button.style.overflow = 'hidden';
        button.appendChild(ripple);
        
        setTimeout(function() {
          ripple.remove();
        }, 600);
      });
    });
    
    console.log('✓ Custom dropdown behavior initialized');
  });
  
})();
