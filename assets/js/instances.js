(function($) {
    'use strict';
    
    let refreshInterval = null;
    
    $(document).ready(function() {
        initInstances();
    });
    
    function initInstances() {
        if ($('.aicfp-instances-page').length === 0) {
            return;
        }
        
        // Charger les tâches
        loadQueueStatus();
        
        // Auto-refresh toutes les 10 secondes
        refreshInterval = setInterval(loadQueueStatus, 10000);
        
        // Bouton refresh manuel
        $('#aicfp-refresh-tasks').on('click', function() {
            loadQueueStatus();
        });
        
        // Actions sur les tâches
        $(document).on('click', '.aicfp-pause-task', function() {
            performTaskAction('aicfp_pause_task', $(this).data('task-id'));
        });
        
        $(document).on('click', '.aicfp-resume-task', function() {
            performTaskAction('aicfp_resume_task', $(this).data('task-id'));
        });
        
        $(document).on('click', '.aicfp-cancel-task', function() {
            if (confirm('Êtes-vous sûr de vouloir annuler cette tâche ?')) {
                performTaskAction('aicfp_cancel_task', $(this).data('task-id'));
            }
        });
        
        $(document).on('click', '.aicfp-delete-task', function() {
            if (confirm('Êtes-vous sûr de vouloir supprimer cette tâche ?')) {
                performTaskAction('aicfp_delete_task', $(this).data('task-id'));
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
            $container.html('<div class="aicfp-empty-state"><p>📋 Aucune tâche dans la file d\'attente</p></div>');
            return;
        }
        
        let html = '';
        
        tasks.forEach(function(task) {
            html += renderTaskCard(task);
        });
        
        $container.html(html);
    }
    
    function renderTaskCard(task) {
        const statusClass = 'aicfp-status-' + task.status;
        const statusLabel = getStatusLabel(task.status);
        
        let html = '<div class="aicfp-task-card">';
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
        
        // Actions
        html += '  <div class="aicfp-task-actions-modern">';
        
        if (task.status === 'processing' || task.status === 'pending') {
            html += '    <button class="aicfp-btn aicfp-btn-secondary aicfp-pause-task" data-task-id="' + task.id + '">⏸ Pause</button>';
        }
        
        if (task.status === 'paused') {
            html += '    <button class="aicfp-btn aicfp-btn-primary aicfp-resume-task" data-task-id="' + task.id + '">▶ Reprendre</button>';
        }
        
        if (task.status !== 'completed' && task.status !== 'cancelled') {
            html += '    <button class="aicfp-btn aicfp-btn-text aicfp-cancel-task" data-task-id="' + task.id + '">✕ Annuler</button>';
        }
        
        if (task.status === 'completed' || task.status === 'cancelled') {
            html += '    <button class="aicfp-btn aicfp-btn-text aicfp-delete-task" data-task-id="' + task.id + '">🗑 Supprimer</button>';
        }
        
        if (task.post_id) {
            html += '    <a href="post.php?post=' + task.post_id + '&action=edit" class="aicfp-btn aicfp-btn-success">📄 Voir l\'article</a>';
        }
        
        html += '  </div>';
        html += '</div>';
        
        return html;
    }
    
    function performTaskAction(action, taskId) {
        $.ajax({
            url: aicfp_ajax.ajax_url,
            type: 'POST',
            data: {
                action: action,
                nonce: aicfp_ajax.nonce,
                task_id: taskId
            },
            success: function(response) {
                if (response.success) {
                    loadQueueStatus();
                    showNotification('✅ Action effectuée', 'success');
                } else {
                    showNotification('❌ ' + response.data.message, 'error');
                }
            },
            error: function() {
                showNotification('❌ Une erreur est survenue', 'error');
            }
        });
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
