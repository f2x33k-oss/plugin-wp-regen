(function($) {
    'use strict';
    
    let selectedPinterestImages = [];
    
    $(document).ready(function() {
        initAlbumsRecettes();
    });
    
    function initAlbumsRecettes() {
        if ($('#aicfp-albums-recettes-form').length === 0) {
            return;
        }
        
        // Calculateur en temps réel
        $('#aicfp_title').on('input change', function() {
            calculateEstimate();
        });
        
        $('#aicfp_generate_text').on('change', function() {
            calculateEstimate();
            togglePublishOption();
        });
        
        // Calcul initial
        calculateEstimate();
        togglePublishOption();
        
        // Sélecteur d'API
        initApiSelector();
        
        // Gestion des images individuelles
        initImageFields();
        
        // Suggestions de titres
        initTitleSuggestions();
        
        // Pinterest
        initPinterestSearch();
        
        // Soumission du formulaire
        $('#aicfp-albums-recettes-form').on('submit', function(e) {
            e.preventDefault();
            submitAlbumRecettes();
        });
    }
    
    function calculateEstimate() {
        const title = $('#aicfp_title').val();
        const generateText = $('#aicfp_generate_text').is(':checked');
        const imageApi = $('#aicfp_image_api').val();
        
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
                generate_text: generateText,
                image_api: imageApi
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
    
    function togglePublishOption() {
        if ($('#aicfp_generate_text').is(':checked')) {
            $('#aicfp-publish-option-row').fadeIn();
        } else {
            $('#aicfp-publish-option-row').fadeOut();
            $('#aicfp_publish_article').prop('checked', false);
        }
    }
    
    function initApiSelector() {
        $('#aicfp_image_api').on('change', function() {
            const selectedApi = $(this).val();
            
            // Masquer toutes les infos
            $('.aicfp-api-info-content').hide();
            
            // Afficher l'info de l'API sélectionnée
            $('.aicfp-api-info-content[data-api="' + selectedApi + '"]').fadeIn();
            
            // Recalculer l'estimation avec le nouveau coût
            calculateEstimate();
        });
    }
    
    function getApiCostPerImage() {
        const api = $('#aicfp_image_api').val();
        const costs = {
            'midjourney': 0.05,
            'sdxl': 0.01,
            'sdxl-food': 0.02,
            'sdxl-finetuned': 0.02,
            'dalle': 0.04,
            'nanobanana': 0.02,
            'replicate': 0.03,
            'flux-pro': 0.03
        };
        return costs[api] || 0.05;
    }
    
    function initImageFields() {
        // Tabs
        $('.aicfp-tab-btn').on('click', function() {
            const tab = $(this).data('tab');
            
            $('.aicfp-tab-btn').removeClass('active');
            $(this).addClass('active');
            
            $('.aicfp-tab-content').removeClass('active');
            $('#tab-' + tab).addClass('active');
        });
        
        // Ajouter un nouveau champ d'image
        $('#aicfp-add-image-field').on('click', function() {
            if ($('.aicfp-image-row').length >= 10) {
                showNotification('Vous pouvez ajouter maximum 10 images de référence.', 'warning');
                return;
            }
            
            const newRow = `
                <div class="aicfp-image-row">
                    <input type="file" 
                           name="reference_images[]" 
                           accept="image/*"
                           class="aicfp-single-image aicfp-file-input">
                    <div class="aicfp-preview"></div>
                    <button type="button" class="aicfp-btn-remove">×</button>
                </div>
            `;
            
            $('#aicfp-individual-images').append(newRow);
        });
        
        // Supprimer une ligne d'image
        $(document).on('click', '.aicfp-btn-remove', function() {
            const $row = $(this).closest('.aicfp-image-row');
            if ($('.aicfp-image-row').length > 1) {
                $row.fadeOut(300, function() {
                    $(this).remove();
                });
            } else {
                $row.find('.aicfp-single-image').val('');
                $row.find('.aicfp-preview').removeClass('has-image').css('background-image', '');
                $(this).fadeOut();
            }
        });
        
        // Prévisualisation des images
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
    
    function initTitleSuggestions() {
        $('#aicfp-suggest-title, #aicfp-reload-suggestions').on('click', function() {
            const $btn = $(this);
            const originalHtml = $btn.html();
            
            $btn.prop('disabled', true).html('<span class="dashicons dashicons-update-alt aicfp-spin"></span> Chargement...');
            
            $.ajax({
                url: aicfp_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'aicfp_suggest_titles',
                    nonce: aicfp_ajax.nonce
                },
                success: function(response) {
                    if (response.success && response.data.suggestions) {
                        displayTitleSuggestions(response.data.suggestions);
                    } else {
                        showNotification(response.data.message || 'Impossible de générer des suggestions', 'error');
                    }
                },
                error: function() {
                    showNotification('Erreur lors de la génération des suggestions', 'error');
                },
                complete: function() {
                    $btn.prop('disabled', false).html(originalHtml);
                }
            });
        });
    }
    
    function displayTitleSuggestions(suggestions) {
        const $list = $('#aicfp-suggestions-list');
        $list.empty();
        
        suggestions.forEach(function(suggestion) {
            const $button = $('<button>')
                .attr('type', 'button')
                .addClass('aicfp-suggestion-button')
                .html('<span class="dashicons dashicons-yes"></span> ' + suggestion)
                .on('click', function() {
                    $('#aicfp_title').val(suggestion).trigger('change');
                    $('#aicfp-title-suggestions').slideUp();
                    showNotification('Titre sélectionné avec succès', 'success');
                });
            
            $list.append($button);
        });
        
        $('#aicfp-title-suggestions').slideDown();
    }
    
    function initPinterestSearch() {
        $('#aicfp-search-pinterest').on('click', function() {
            const query = $('#aicfp_pinterest_search').val().trim();
            
            if (!query) {
                showNotification('Veuillez entrer un terme de recherche', 'warning');
                return;
            }
            
            const $btn = $(this);
            const originalHtml = $btn.html();
            
            $btn.prop('disabled', true).html('<span class="dashicons dashicons-update-alt aicfp-spin"></span> Recherche...');
            
            $.ajax({
                url: aicfp_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'aicfp_search_pinterest',
                    nonce: aicfp_ajax.nonce,
                    query: query
                },
            success: function(response) {
                if (response.success && response.data.images) {
                    displayPinterestResults(response.data.images);
                    
                    if (response.data.demo) {
                        showNotification('💡 ' + (response.data.message || 'Images de démonstration affichées'), 'info');
                    } else {
                        showNotification('✅ ' + response.data.count + ' images trouvées', 'success');
                    }
                } else {
                    showNotification('❌ ' + (response.data.message || 'Aucune image trouvée'), 'error');
                }
            },
                error: function() {
                    showNotification('Erreur lors de la recherche Pinterest', 'error');
                },
                complete: function() {
                    $btn.prop('disabled', false).html(originalHtml);
                }
            });
        });
        
        // Recherche avec Enter
        $('#aicfp_pinterest_search').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                $('#aicfp-search-pinterest').click();
            }
        });
    }
    
    function displayPinterestResults(images) {
        const $grid = $('#aicfp-pinterest-grid');
        $grid.empty();
        selectedPinterestImages = [];
        
        images.forEach(function(image, index) {
            const $item = $('<div>')
                .addClass('aicfp-pinterest-item')
                .data('image', image)
                .data('index', index);
            
            const $img = $('<img>')
                .attr('src', image.thumbnail || image.url)
                .attr('alt', image.title || 'Pinterest image')
                .attr('loading', 'lazy');
            
            $item.append($img);
            
            $item.on('click', function() {
                $(this).toggleClass('selected');
                updatePinterestCount();
            });
            
            $grid.append($item);
        });
        
        $('#aicfp-pinterest-results').slideDown();
        updatePinterestCount();
    }
    
    function updatePinterestCount() {
        const count = $('.aicfp-pinterest-item.selected').length;
        $('#aicfp-selected-count').text(count);
    }
    
    $('#aicfp-clear-pinterest').on('click', function() {
        $('.aicfp-pinterest-item').removeClass('selected');
        updatePinterestCount();
    });
    
    $('#aicfp-import-pinterest').on('click', function() {
        const $selected = $('.aicfp-pinterest-item.selected');
        
        if ($selected.length === 0) {
            showNotification('Veuillez sélectionner au moins une image', 'warning');
            return;
        }
        
        selectedPinterestImages = [];
        $selected.each(function() {
            selectedPinterestImages.push($(this).data('image'));
        });
        
        $('#aicfp-pinterest-results').slideUp();
        $('.aicfp-upload-option').eq(1).hide();
        
        showNotification('✅ ' + selectedPinterestImages.length + ' images Pinterest importées', 'success');
        
        // Badge Pinterest
        if (!$('#aicfp-pinterest-badge').length) {
            const badge = '<span id="aicfp-pinterest-badge" class="aicfp-badge aicfp-badge-pinterest">📌 ' + selectedPinterestImages.length + ' images Pinterest</span>';
            $('.aicfp-title-field-wrapper').append(badge);
        } else {
            $('#aicfp-pinterest-badge').html('📌 ' + selectedPinterestImages.length + ' images Pinterest');
        }
    });
    
    function submitAlbumRecettes() {
        const $form = $('#aicfp-albums-recettes-form');
        const $submitBtn = $('#aicfp-submit-btn');
        const $resultMessage = $('#aicfp-result-message');
        
        // Validation
        const title = $('#aicfp_title').val().trim();
        const email = $('#aicfp_email').val().trim();
        
        if (!title) {
            showNotification('Le titre est requis', 'error');
            return;
        }
        
        if (!email) {
            showNotification('L\'email est requis', 'error');
            return;
        }
        
        // Désactiver le bouton
        $submitBtn.prop('disabled', true).html('<span class="dashicons dashicons-update-alt aicfp-spin"></span> Traitement en cours...');
        
        // Créer FormData
        const formData = new FormData($form[0]);
        formData.append('action', 'aicfp_submit_generation');
        formData.append('nonce', aicfp_ajax.nonce);
        
        // Ajouter les images Pinterest si présentes
        if (selectedPinterestImages.length > 0) {
            formData.append('pinterest_images', JSON.stringify(selectedPinterestImages));
        }
        
        $.ajax({
            url: aicfp_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showNotification('✅ ' + response.data.message, 'success');
                    
                    // Réinitialiser le formulaire
                    $form[0].reset();
                    selectedPinterestImages = [];
                    $('#aicfp-pinterest-badge').remove();
                    $('.aicfp-image-preview').removeClass('has-image').css('background-image', '');
                    $('.aicfp-remove-image').hide();
                    calculateEstimate();
                    
                    // Rediriger vers Instances
                    setTimeout(function() {
                        window.location.href = 'admin.php?page=aicfp-instances';
                    }, 2000);
                } else {
                    showNotification('❌ ' + response.data.message, 'error');
                }
            },
            error: function() {
                showNotification('❌ Une erreur est survenue. Veuillez réessayer.', 'error');
            },
            complete: function() {
                $submitBtn.prop('disabled', false).html('<span class="dashicons dashicons-hammer"></span> Lancer la génération');
            }
        });
    }
    
    function showNotification(message, type) {
        const $notification = $('<div>')
            .addClass('aicfp-notification aicfp-notification-' + type)
            .html(message);
        
        $('.wrap').prepend($notification);
        
        $notification.fadeIn(300);
        
        setTimeout(function() {
            $notification.fadeOut(300, function() {
                $(this).remove();
            });
        }, 5000);
    }
    
})(jQuery);
