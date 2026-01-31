(function($) {
    'use strict';
    
    // Variables globales
    let refreshInterval = null;
    
    /**
     * Initialisation
     */
    $(document).ready(function() {
        initGeneratePage();
        initInstancesPage();
    });
    
    /**
     * Initialiser la page de génération
     */
    function initGeneratePage() {
        if ($('#aicfp-generate-form').length === 0) {
            return;
        }
        
        // Calculer l'estimation en temps réel
        $('#aicfp_title').on('change keyup', debounce(function() {
            calculateEstimate();
        }, 500));
        
        $('#aicfp_generate_text').on('change', function() {
            calculateEstimate();
        });
        
        $('#aicfp_reference_zip').on('change', function() {
            calculateEstimate();
        });
        
        // Calcul initial
        calculateEstimate();
        
        // Soumettre le formulaire
        $('#aicfp-generate-form').on('submit', function(e) {
            e.preventDefault();
            submitGeneration();
        });
    }
    
    /**
     * Initialiser la page des instances
     */
    function initInstancesPage() {
        if ($('.aicfp-instances-page').length === 0) {
            return;
        }
        
        // Charger les tâches
        loadQueueStatus();
        
        // Actualiser automatiquement toutes les 10 secondes
        refreshInterval = setInterval(function() {
            loadQueueStatus();
        }, 10000);
        
        // Bouton actualiser
        $('#aicfp-refresh-tasks').on('click', function() {
            loadQueueStatus();
        });
        
        // Actions sur les tâches (délégation d'événements)
        $(document).on('click', '.aicfp-pause-task', function() {
            const taskId = $(this).data('task-id');
            pauseTask(taskId);
        });
        
        $(document).on('click', '.aicfp-resume-task', function() {
            const taskId = $(this).data('task-id');
            resumeTask(taskId);
        });
        
        $(document).on('click', '.aicfp-cancel-task', function() {
            const taskId = $(this).data('task-id');
            if (confirm(aicfp_ajax.strings.confirm_cancel)) {
                cancelTask(taskId);
            }
        });
        
        $(document).on('click', '.aicfp-delete-task', function() {
            const taskId = $(this).data('task-id');
            if (confirm(aicfp_ajax.strings.confirm_delete)) {
                deleteTask(taskId);
            }
        });
    }
    
    /**
     * Calculer l'estimation
     */
    function calculateEstimate() {
        const title = $('#aicfp_title').val();
        const generateText = $('#aicfp_generate_text').is(':checked');
        
        if (!title) {
            $('#aicfp-estimated-items').text('-');
            $('#aicfp-estimated-cost').text('$0.00');
            $('#aicfp-estimated-time').text('0 min');
            return;
        }
        
        $.ajax({
            url: aicfp_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'aicfp_calculate_estimate',
                nonce: aicfp_ajax.nonce,
                title: title,
                generate_text: generateText
            },
            success: function(response) {
                if (response.success) {
                    $('#aicfp-estimated-items').text(response.data.item_count);
                    $('#aicfp-estimated-cost').text('$' + response.data.cost);
                    $('#aicfp-estimated-time').text(response.data.time + ' min');
                }
            }
        });
    }
    
    /**
     * Soumettre la génération
     */
    function submitGeneration() {
        const $form = $('#aicfp-generate-form');
        const $submitBtn = $('#aicfp-submit-btn');
        const $resultMessage = $('#aicfp-result-message');
        
        // Désactiver le bouton
        $submitBtn.prop('disabled', true).html('<span class="dashicons dashicons-update-alt"></span> Traitement en cours...');
        
        // Créer un FormData pour gérer l'upload de fichier
        const formData = new FormData($form[0]);
        formData.append('action', 'aicfp_submit_generation');
        formData.append('nonce', aicfp_ajax.nonce);
        
        $.ajax({
            url: aicfp_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $resultMessage
                        .removeClass('aicfp-result-error')
                        .addClass('aicfp-result-success')
                        .html('<strong>Succès !</strong> ' + response.data.message)
                        .show();
                    
                    // Réinitialiser le formulaire
                    $form[0].reset();
                    calculateEstimate();
                    
                    // Rediriger vers la page des instances après 2 secondes
                    setTimeout(function() {
                        window.location.href = 'admin.php?page=aicfp-instances';
                    }, 2000);
                } else {
                    $resultMessage
                        .removeClass('aicfp-result-success')
                        .addClass('aicfp-result-error')
                        .html('<strong>Erreur !</strong> ' + response.data.message)
                        .show();
                }
            },
            error: function() {
                $resultMessage
                    .removeClass('aicfp-result-success')
                    .addClass('aicfp-result-error')
                    .html('<strong>Erreur !</strong> ' + aicfp_ajax.strings.error_occurred)
                    .show();
            },
            complete: function() {
                // Réactiver le bouton
                $submitBtn.prop('disabled', false).html('<span class="dashicons dashicons-hammer"></span> Lancer la génération');
            }
        });
    }
    
    /**
     * Charger le statut de la file d'attente
     */
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
    
    /**
     * Mettre à jour les statistiques
     */
    function updateStats(stats) {
        $('#stat-pending').text(stats.pending);
        $('#stat-processing').text(stats.processing);
        $('#stat-completed').text(stats.completed);
        $('#stat-paused').text(stats.paused);
    }
    
    /**
     * Render les tâches
     */
    function renderTasks(tasks) {
        const $container = $('#aicfp-tasks-container');
        
        if (tasks.length === 0) {
            $container.html('<p class="aicfp-no-tasks">Aucune tâche dans la file d\'attente.</p>');
            return;
        }
        
        let html = '';
        
        tasks.forEach(function(task) {
            const statusClass = 'aicfp-status-' + task.status;
            const statusLabel = getStatusLabel(task.status);
            
            html += '<div class="aicfp-task-item">';
            html += '  <div class="aicfp-task-header">';
            html += '    <div>';
            html += '      <div class="aicfp-task-title">' + escapeHtml(task.title) + '</div>';
            html += '      <div class="aicfp-task-meta">';
            html += '        ID: #' + task.id + ' | ';
            html += '        Items: ' + task.current_item + '/' + task.total_items + ' | ';
            html += '        Email: ' + escapeHtml(task.email) + ' | ';
            html += '        Créé: ' + task.created_at;
            html += '      </div>';
            html += '    </div>';
            html += '    <span class="aicfp-task-status ' + statusClass + '">' + statusLabel + '</span>';
            html += '  </div>';
            
            // Barre de progression pour les tâches en cours
            if (task.status === 'processing' || task.status === 'paused') {
                html += '  <div class="aicfp-progress-bar">';
                html += '    <div class="aicfp-progress-fill" style="width: ' + task.progress + '%"></div>';
                html += '  </div>';
                html += '  <div class="aicfp-progress-text">' + task.progress + '% complété</div>';
            }
            
            // Informations supplémentaires
            html += '  <div class="aicfp-task-meta" style="margin-top: 10px;">';
            html += '    Coût estimé: $' + task.cost_estimate + ' | ';
            html += '    Temps estimé: ' + task.time_estimate + ' min';
            if (task.error_count > 0) {
                html += ' | <span style="color: #d63638;">⚠ ' + task.error_count + ' erreur(s)</span>';
            }
            html += '  </div>';
            
            // Actions
            html += '  <div class="aicfp-task-actions">';
            
            if (task.status === 'processing' || task.status === 'pending') {
                html += '    <button class="button aicfp-pause-task" data-task-id="' + task.id + '">Pause</button>';
            }
            
            if (task.status === 'paused') {
                html += '    <button class="button aicfp-resume-task" data-task-id="' + task.id + '">Reprendre</button>';
            }
            
            if (task.status !== 'completed' && task.status !== 'cancelled') {
                html += '    <button class="button aicfp-cancel-task" data-task-id="' + task.id + '">Annuler</button>';
            }
            
            if (task.status === 'completed' || task.status === 'cancelled') {
                html += '    <button class="button aicfp-delete-task" data-task-id="' + task.id + '">Supprimer</button>';
            }
            
            if (task.post_id) {
                html += '    <a href="post.php?post=' + task.post_id + '&action=edit" class="button">Voir l\'article</a>';
            }
            
            html += '  </div>';
            html += '</div>';
        });
        
        $container.html(html);
    }
    
    /**
     * Mettre en pause une tâche
     */
    function pauseTask(taskId) {
        performTaskAction('aicfp_pause_task', taskId, 'Tâche mise en pause.');
    }
    
    /**
     * Reprendre une tâche
     */
    function resumeTask(taskId) {
        performTaskAction('aicfp_resume_task', taskId, 'Tâche reprise.');
    }
    
    /**
     * Annuler une tâche
     */
    function cancelTask(taskId) {
        performTaskAction('aicfp_cancel_task', taskId, 'Tâche annulée.');
    }
    
    /**
     * Supprimer une tâche
     */
    function deleteTask(taskId) {
        performTaskAction('aicfp_delete_task', taskId, 'Tâche supprimée.');
    }
    
    /**
     * Effectuer une action sur une tâche
     */
    function performTaskAction(action, taskId, successMessage) {
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
                    // Recharger les tâches
                    loadQueueStatus();
                } else {
                    alert('Erreur: ' + response.data.message);
                }
            },
            error: function() {
                alert(aicfp_ajax.strings.error_occurred);
            }
        });
    }
    
    /**
     * Obtenir le label du statut
     */
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
    
    /**
     * Échapper les caractères HTML
     */
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
    
    /**
     * Debounce function
     */
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    /**
     * Nettoyer lors de la fermeture de la page
     */
    $(window).on('beforeunload', function() {
        if (refreshInterval) {
            clearInterval(refreshInterval);
        }
    });
    
})(jQuery);
