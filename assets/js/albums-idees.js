(function($) {
    'use strict';
    
    $(document).ready(function() {
        initAlbumsIdees();
    });
    
    function initAlbumsIdees() {
        if ($('#aicfp-albums-idees-form').length === 0) {
            return;
        }
        
        // Calcul automatique de l'estimation
        $('#aicfp_idees_title').on('input change', function() {
            updateEstimation();
        });
        
        updateEstimation();
        
        // Tabs
        $('.aicfp-tab-btn').on('click', function() {
            const tab = $(this).data('tab');
            
            $('.aicfp-tab-btn').removeClass('active');
            $(this).addClass('active');
            
            $('.aicfp-tab-content').removeClass('active');
            $('#tab-' + tab).addClass('active');
        });
        
        // Gestion des images
        initImageFields();
        
        // Soumission du formulaire
        $('#aicfp-albums-idees-form').on('submit', function(e) {
            e.preventDefault();
            submitAlbumIdees();
        });
    }
    
    function updateEstimation() {
        const title = $('#aicfp_idees_title').val();
        const match = title.match(/(\d+)/);
        const itemCount = match ? parseInt(match[1]) : 10;
        
        $('#aicfp-idees-estimated-items').text(itemCount);
        $('#aicfp-idees-estimated-cost').text('$' + (itemCount * 0.05).toFixed(2));
        $('#aicfp-idees-estimated-time').text(Math.ceil(itemCount * 2) + ' min');
    }
    
    function initImageFields() {
        // Upload multiple
        $('#aicfp_idees_reference_files').on('change', function() {
            const filesCount = this.files.length;
            if (filesCount > 0) {
                showNotification('✅ ' + filesCount + ' fichier(s) sélectionné(s)', 'success');
            }
        });
        
        // Ajouter image individuelle
        $('#aicfp-idees-add-image-field').on('click', function() {
            if ($('.aicfp-image-row').length >= 10) {
                showNotification('Maximum 10 images', 'warning');
                return;
            }
            
            const newRow = `
                <div class="aicfp-image-row">
                    <input type="file" name="individual_images[]" accept="image/*" class="aicfp-single-image aicfp-file-input">
                    <div class="aicfp-preview"></div>
                    <button type="button" class="aicfp-btn-remove">×</button>
                </div>
            `;
            
            $('#aicfp-idees-individual-images').append(newRow);
        });
        
        // Supprimer une ligne
        $(document).on('click', '.aicfp-btn-remove', function() {
            const $row = $(this).closest('.aicfp-image-row');
            if ($('.aicfp-image-row').length > 1) {
                $row.fadeOut(300, function() { $(this).remove(); });
            } else {
                $row.find('.aicfp-single-image').val('');
                $row.find('.aicfp-preview').removeClass('has-image').css('background-image', '');
                $(this).fadeOut();
            }
        });
        
        // Prévisualisation
        $(document).on('change', '.aicfp-single-image', function() {
            const file = this.files[0];
            const $preview = $(this).siblings('.aicfp-preview');
            const $removeBtn = $(this).siblings('.aicfp-btn-remove');
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $preview.css('background-image', 'url(' + e.target.result + ')');
                    $preview.addClass('has-image');
                    $removeBtn.fadeIn();
                }
                reader.readAsDataURL(file);
            } else {
                $preview.removeClass('has-image').css('background-image', '');
                $removeBtn.fadeOut();
            }
        });
    }
    
    function submitAlbumIdees() {
        const $form = $('#aicfp-albums-idees-form');
        const $submitBtn = $('#aicfp-idees-submit-btn');
        const $result = $('#aicfp-idees-result-message');
        
        // Validation
        const title = $('#aicfp_idees_title').val().trim();
        const email = $('#aicfp_idees_email').val().trim();
        
        if (!title) {
            showNotification('Le titre est requis', 'error');
            return;
        }
        
        if (!email) {
            showNotification('L\'email est requis', 'error');
            return;
        }
        
        $submitBtn.prop('disabled', true).html('<span class="dashicons dashicons-update-alt aicfp-spin"></span> Traitement en cours...');
        
        const formData = new FormData($form[0]);
        formData.append('action', 'aicfp_submit_album_idees');
        formData.append('nonce', aicfp_ajax.nonce);
        
        $.ajax({
            url: aicfp_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showNotification('✅ ' + response.data.message, 'success');
                    
                    // Message de redirection
                    setTimeout(function() {
                        showNotification('🔄 Redirection vers le suivi des générations...', 'info');
                    }, 1500);
                    
                    // Rediriger vers Instances avec highlight
                    setTimeout(function() {
                        window.location.href = 'admin.php?page=aicfp-instances&highlight=' + (response.data.task_id || '');
                    }, 2500);
                } else {
                    showNotification('❌ ' + response.data.message, 'error');
                }
            },
            error: function() {
                showNotification('❌ Une erreur est survenue', 'error');
            },
            complete: function() {
                $submitBtn.prop('disabled', false).html('<span class="dashicons dashicons-images-alt2"></span> Générer l\'album d\'idées');
            }
        });
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
    
})(jQuery);
