(function($) {
    'use strict';
    
    let refreshInterval = null;
    
    $(document).ready(function() {
        initInstances();
    });
    
    function initInstances() {
        if ($('.aicfp-instances-page').length === 0 && $('.aicfp-instances-modern').length === 0) {
            return;
        }
        
        console.log('AICFP: Initialisation Instances OK');
        
        // Charger les tâches IMMÉDIATEMENT
        loadQueueStatus();
        
        // Auto-refresh toutes les 5 secondes (plus rapide)
        refreshInterval = setInterval(loadQueueStatus, 5000);
        
        // Bouton refresh manuel
        $('#aicfp-refresh-tasks').on('click', function() {
            $(this).addClass('aicfp-spin');
            loadQueueStatus();
            setTimeout(function() {
                $('#aicfp-refresh-tasks .dashicons').removeClass('aicfp-spin');
            }, 1000);
        });
        
        // Actions sur les tâches
        $(document).on('click', '.aicfp-start-task', function() {
            performTaskAction('aicfp_start_task', $(this).data('task-id'), 'Génération démarrée');
        });
        
        $(document).on('click', '.aicfp-pause-task', function() {
            performTaskAction('aicfp_pause_task', $(this).data('task-id'), 'Génération mise en pause');
        });
        
        $(document).on('click', '.aicfp-resume-task', function() {
            performTaskAction('aicfp_resume_task', $(this).data('task-id'), 'Génération reprise');
        });
        
        $(document).on('click', '.aicfp-cancel-task', function() {
            if (confirm('⚠️ Êtes-vous sûr de vouloir arrêter cette génération ? Cette action est irréversible.')) {
                performTaskAction('aicfp_cancel_task', $(this).data('task-id'), 'Génération arrêtée');
            }
        });
        
        $(document).on('click', '.aicfp-delete-task', function() {
            if (confirm('🗑️ Êtes-vous sûr de vouloir supprimer définitivement cette tâche ?')) {
                performTaskAction('aicfp_delete_task', $(this).data('task-id'), 'Tâche supprimée');
            }
        });
    }
    
    function loadQueueStatus() {
        $.ajax({
            url: aicfp_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'aicfp_get_queue_status',
                nonce: aicfp_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    updateStats(response.data.stats);
                    renderTasks(response.data.tasks);
                }
            }
        });
    }
    
    function updateStats(stats) {
        $('#stat-pending').text(stats.pending);
        $('#stat-processing').text(stats.processing);
        $('#stat-completed').text(stats.completed);
        $('#stat-paused').text(stats.paused);
    }
    
    function renderTasks(tasks) {
        const $container = $('#aicfp-tasks-container');
        
        if (tasks.length === 0) {
            $container.html('<div class="aicfp-empty-state"><p>📋 Aucune tâche dans la file d\'attente</p><p class="description">Créez votre premier album depuis le menu Albums Recettes ou Albums Idées</p></div>');
            return;
        }
        
        const urlParams = new URLSearchParams(window.location.search);
        const highlightId = urlParams.get('highlight');
        
        // Séparer les tâches en cours et terminées
        const activeTasks = tasks.filter(t => t.status === 'processing' || t.status === 'pending' || t.status === 'paused');
        const completedTasks = tasks.filter(t => t.status === 'completed' || t.status === 'cancelled' || t.status === 'failed');
        
        let html = '';
        
        // Section tâches actives EN HAUT
        if (activeTasks.length > 0) {
            html += '<div class="aicfp-active-section">';
            html += '  <h2 class="aicfp-section-title">⚡ Génération(s) en cours</h2>';
            activeTasks.forEach(function(task) {
                html += renderTaskCard(task, highlightId, true); // true = gamified
            });
            html += '</div>';
        }
        
        // Section historique EN BAS
        if (completedTasks.length > 0) {
            html += '<div class="aicfp-history-section">';
            html += '  <h2 class="aicfp-section-title">📚 Historique des générations</h2>';
            completedTasks.forEach(function(task) {
                html += renderTaskCard(task, highlightId, false); // false = compact
            });
            html += '</div>';
        }
        
        $container.html(html);
        
        // Scroll vers highlight SEULEMENT la première fois (pas à chaque refresh)
        if (highlightId && !sessionStorage.getItem('aicfp_scrolled_' + highlightId)) {
            setTimeout(function() {
                const $highlighted = $('.aicfp-task-card[data-task-id="' + highlightId + '"]');
                if ($highlighted.length) {
                    $('html, body').animate({
                        scrollTop: Math.max(0, $highlighted.offset().top - 150)
                    }, 500);
                    // Marquer comme scrollé pour cette session
                    sessionStorage.setItem('aicfp_scrolled_' + highlightId, 'true');
                }
            }, 200);
        }
    }
    
    function renderTaskCard(task, highlightId, gamified) {
        const statusClass = 'aicfp-status-' + task.status;
        const statusLabel = getStatusLabel(task.status);
        const isHighlighted = highlightId && task.id == highlightId;
        const isActive = task.status === 'processing' || task.status === 'pending' || task.status === 'paused';
        
        let html = '<div class="aicfp-task-card';
        html += (isHighlighted ? ' aicfp-task-highlighted' : '');
        html += (gamified && isActive ? ' aicfp-task-gamified' : '');
        html += '" data-task-id="' + task.id + '" data-status="' + task.status + '">';
        html += '  <div class="aicfp-task-header-modern">';
        html += '    <div>';
        html += '      <div class="aicfp-task-title-modern">' + escapeHtml(task.title) + '</div>';
        html += '      <div class="aicfp-task-meta-modern">';
        html += '        #' + task.id + ' • ' + task.current_item + '/' + task.total_items + ' items';
        html += '        • ' + escapeHtml(task.email);
        html += '      </div>';
        html += '    </div>';
        html += '    <span class="aicfp-status-badge ' + statusClass + '">' + statusLabel + '</span>';
        html += '  </div>';
        
        // Barre de progression
        if (task.status === 'processing' || task.status === 'paused') {
            html += '  <div class="aicfp-progress-modern">';
            html += '    <div class="aicfp-progress-fill-modern" style="width: ' + task.progress + '%"></div>';
            html += '  </div>';
            html += '  <div class="aicfp-progress-text-modern">' + task.progress + '% complété</div>';
        }
        
        // Détails supplémentaires
        html += '  <div class="aicfp-task-details">';
        html += '    <div class="aicfp-detail-item">';
        html += '      <span class="aicfp-detail-label">💰 Coût:</span>';
        html += '      <span class="aicfp-detail-value">$' + task.cost_estimate + '</span>';
        html += '    </div>';
        html += '    <div class="aicfp-detail-item">';
        html += '      <span class="aicfp-detail-label">⏱️ Temps:</span>';
        html += '      <span class="aicfp-detail-value">' + task.time_estimate + ' min</span>';
        html += '    </div>';
        html += '    <div class="aicfp-detail-item">';
        html += '      <span class="aicfp-detail-label">📅 Créé:</span>';
        html += '      <span class="aicfp-detail-value">' + formatDate(task.created_at) + '</span>';
        html += '    </div>';
        if (task.started_at && task.status !== 'pending') {
            html += '    <div class="aicfp-detail-item">';
            html += '      <span class="aicfp-detail-label">🚀 Démarré:</span>';
            html += '      <span class="aicfp-detail-value">' + formatDate(task.started_at) + '</span>';
            html += '    </div>';
        }
        html += '  </div>';
        
        // Actions
        html += '  <div class="aicfp-task-actions-modern">';
        
        // Bouton Démarrer (pour tâches en attente)
        if (task.status === 'pending') {
            html += '    <button class="aicfp-btn aicfp-btn-success aicfp-start-task" data-task-id="' + task.id + '">';
            html += '      <span class="dashicons dashicons-controls-play"></span>';
            html += '      Démarrer maintenant';
            html += '    </button>';
        }
        
        // Bouton Pause (pour tâches en cours)
        if (task.status === 'processing') {
            html += '    <button class="aicfp-btn aicfp-btn-warning aicfp-pause-task" data-task-id="' + task.id + '">';
            html += '      <span class="dashicons dashicons-controls-pause"></span>';
            html += '      Mettre en pause';
            html += '    </button>';
        }
        
        // Bouton Reprendre (pour tâches en pause)
        if (task.status === 'paused') {
            html += '    <button class="aicfp-btn aicfp-btn-primary aicfp-resume-task" data-task-id="' + task.id + '">';
            html += '      <span class="dashicons dashicons-controls-play"></span>';
            html += '      Reprendre';
            html += '    </button>';
        }
        
        // Bouton Arrêter (pour toutes sauf terminées)
        if (task.status !== 'completed' && task.status !== 'cancelled') {
            html += '    <button class="aicfp-btn aicfp-btn-danger aicfp-cancel-task" data-task-id="' + task.id + '">';
            html += '      <span class="dashicons dashicons-no"></span>';
            html += '      Arrêter';
            html += '    </button>';
        }
        
        // Bouton Supprimer (pour terminées)
        if (task.status === 'completed' || task.status === 'cancelled') {
            html += '    <button class="aicfp-btn aicfp-btn-text aicfp-delete-task" data-task-id="' + task.id + '">';
            html += '      <span class="dashicons dashicons-trash"></span>';
            html += '      Supprimer';
            html += '    </button>';
        }
        
        // Liens de téléchargement pour tâches terminées
        if (task.status === 'completed') {
            html += '    <div class="aicfp-download-links" style="margin-top: 15px; padding-top: 15px; border-top: 2px dashed #e5e7eb;">';
            html += '      <h4 style="margin: 0 0 10px 0; font-size: 13px; color: #6b7280; text-transform: uppercase;">📦 Téléchargements</h4>';
            html += '      <div style="display: flex; gap: 10px; flex-wrap: wrap;">';
            
            // Lien article WordPress
            if (task.post_id) {
                html += '        <a href="post.php?post=' + task.post_id + '&action=edit" class="aicfp-btn aicfp-btn-success aicfp-btn-small">';
                html += '          <span class="dashicons dashicons-edit"></span> Article WP';
                html += '        </a>';
            }
            
            // Lien Google Drive (images ZIP) - Placeholder pour l'instant
            html += '        <button class="aicfp-btn aicfp-btn-primary aicfp-btn-small aicfp-download-images" data-task-id="' + task.id + '">';
            html += '          <span class="dashicons dashicons-download"></span> Images (ZIP)';
            html += '        </button>';
            
            // Lien Google Docs (textes)
            if (task.generate_text) {
                html += '        <button class="aicfp-btn aicfp-btn-secondary aicfp-btn-small aicfp-download-texts" data-task-id="' + task.id + '">';
                html += '          <span class="dashicons dashicons-media-document"></span> Textes (Doc)';
                html += '        </button>';
            }
            
            html += '      </div>';
            html += '    </div>';
        }
        
        // Boutons standards
        html += '  </div>';
        html += '</div>';
        
        return html;
    }
    
    function performTaskAction(action, taskId, successMessage) {
        successMessage = successMessage || 'Action effectuée';
        
        // Afficher une notification de chargement
        const $loader = $('<div class="aicfp-notification aicfp-notification-info">⏳ Traitement en cours...</div>');
        $('body').append($loader);
        $loader.fadeIn(200);
        
        $.ajax({
            url: aicfp_ajax.ajax_url,
            type: 'POST',
            data: {
                action: action,
                nonce: aicfp_ajax.nonce,
                task_id: taskId
            },
            success: function(response) {
                $loader.fadeOut(200, function() { $(this).remove(); });
                
                if (response.success) {
                    loadQueueStatus();
                    showNotification('✅ ' + successMessage, 'success');
                } else {
                    showNotification('❌ ' + response.data.message, 'error');
                }
            },
            error: function() {
                $loader.fadeOut(200, function() { $(this).remove(); });
                showNotification('❌ Une erreur est survenue', 'error');
            }
        });
    }
    
    function formatDate(dateString) {
        if (!dateString) return '-';
        
        const date = new Date(dateString);
        const now = new Date();
        const diff = Math.floor((now - date) / 1000); // Différence en secondes
        
        if (diff < 60) {
            return 'Il y a ' + diff + 's';
        } else if (diff < 3600) {
            return 'Il y a ' + Math.floor(diff / 60) + ' min';
        } else if (diff < 86400) {
            return 'Il y a ' + Math.floor(diff / 3600) + 'h';
        } else {
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            return day + '/' + month + ' ' + hours + ':' + minutes;
        }
    }
    
    function getStatusLabel(status) {
        const labels = {
            'pending': 'En attente',
            'processing': 'En cours',
            'completed': 'Terminée',
            'paused': 'En pause',
            'cancelled': 'Annulée',
            'failed': 'Échouée'
        };
        return labels[status] || status;
    }
    
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
    }
    
    function showNotification(message, type) {
        const $notification = $('<div>')
            .addClass('aicfp-notification aicfp-notification-' + type)
            .html(message);
        
        $('body').append($notification);
        $notification.fadeIn(300);
        
        setTimeout(function() {
            $notification.fadeOut(300, function() {
                $(this).remove();
            });
        }, 5000);
    }
    
    // Cleanup
    $(window).on('beforeunload', function() {
        if (refreshInterval) {
            clearInterval(refreshInterval);
        }
    });
    
})(jQuery);
