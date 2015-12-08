src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js";

    $(document).ready(function(){

        var elementSelectSelector = $('#' + nameSpace + '-' + id);
        var elementSelect = elementSelectSelector.data('elementSelect');


        elementSelect.on('selectElements', function(elements) {
            var assets = elements.elements;

            $('#fields-imgCropper-spinner').show();
            $('#fields-cropped-images').empty();
            if(assets.length) {
                for (var i = 0; i < assets.length; i++) {

                    var asset = assets[i];
                    var data = {
                        assetId: asset.id,
                        entryId: entryId,
                        name: name
                    };

                    // get the crops
                    Craft.postActionRequest('imageCropper/getCroppedImages', data, function(response) {
                        if(response.success){
                            $('#fields-imgCropper-spinner').hide();
                            var htmlElements = response.data;
                            for(var j = 0; j<htmlElements.length; j++){
                                var html = $(htmlElements[j]);
                                (function(html){
                                    $('#fields-cropped-images').fadeIn('fast', function(){
                                        $(this).append(html);
                                    });
                                })(html);
                            }
                            Craft.cp.displayNotice(Craft.t(response.message));
                        }
                        else{
                            Craft.cp.displayError(Craft.t(response.message));
                        }
                    });
                }
            }
        });

        elementSelect.on('removeElements', function() {
            $('#fields-cropped-images').fadeOut('fast', function(){
                $(this).empty();
            });
        });
        Craft.CropImageModal = Garnish.Modal.extend(
            {
                $element: null,
                $selectedItems: null,
                settings: null,

                $container: null,
                $body: null,
                $footerSpinner: null,
                $buttonsLeft: null,
                $buttonsRight: null,
                $cancelBtn: null,
                $saveBtn: null,
                $aspectRatioSelect: null,

                areaSelect: null,

                init: function($element, $selectedItems, settings) {
                    this.$element = $element;
                    this.$selectedItems = $selectedItems;

                    //this.desiredWidth = 400;
                    //this.desiredHeight = 280;
                    this.desiredWidth = 600;
                    this.desiredHeight = 420;

                    // Build the modal
                    var $container = $('<div class="modal fitted logo-modal last image-cropper-crop-modal"></div>').appendTo(Garnish.$bod),
                        $footer = $('<div class="footer"/>').appendTo($container);

                    $body = $('<div class="crop-image">' +
                        '<div class="image-chooser">' +
                        '<div class="centeralign">' +
                        '<div class="spinner loadingmore big"></div>' +
                        '</div>' +
                        '</div>' +
                        '</div>').appendTo($container);

                    this.base($container, this.settings);

                    this.$footerSpinner = $('<div class="spinner hidden"/>').appendTo($footer);
                    this.$buttonsLeft = $('<div class="buttons leftalign first"/>').appendTo($footer);

                    this.$buttonsRight = $('<div class="buttons rightalign first"/>').appendTo($footer);
                    this.$cancelBtn = $('<div class="btn">'+Craft.t('Cancel')+'</div>').appendTo(this.$buttonsRight);
                    this.$saveBtn = $('<div class="btn submit">'+Craft.t('Save')+'</div>').appendTo(this.$buttonsRight);

                    this.$inputDiv = $('<div class="buttons"/>').appendTo($footer);

                    this.$body = $body;

                    this.fetchImage(this.$element);

                    this.addListener(this.$cancelBtn, 'activate', 'onFadeOut');
                    this.addListener(this.$saveBtn, 'activate', 'saveImage');
                },

                onFadeOut: function() {
                    this.hide();
                },

                fetchImage: function($element) {
                    var dataId = $element.data('orig-id');

                    var width = $element.data('width');
                    var height = $element.data('height');

                    Craft.postActionRequest('imageCropper/getAssetInput', { assetId: dataId }, $.proxy(function(response, textStatus) {
                        this.$body.find('.spinner').addClass('hidden');

                        if (textStatus == 'success') {

                            var $imgContainer = $(response.html).appendTo(this.$container.find('.image-chooser'));
                            var initialRect = this.getInitRectangle(width, height);

                            // Setup cropping
                            this.$container.find('img').load($.proxy(function() {
                                this.areaSelect = new Craft.CropImageAreaTool(this.$body, {
                                    aspectRatio:  width + ':' + height,
                                    initialRectangle: initialRect
                                });

                                this.areaSelect.showArea(this);

                                this.resize();
                            }, this));
                        }

                    }, this));
                },

                getInitRectangle: function(cropWidth, cropHeight) {
                    var $target = this.$container.find('img');
                    var originalWidth = $target.data('orig-width');
                    var originalHeight = $target.data('orig-height');

                    var rectangleWidth = (cropWidth * $target.width()) / originalWidth;
                    var rectangleHeight = (cropHeight * $target.height()) / originalHeight;

                    if(rectangleHeight > $target.height()){
                        /* @TODO: lets figure out a good way to handle this */
                        alert('Crop height exceeds original image height.');
                    }
                    if(rectangleWidth > $target.width()){
                        /* @TODO: lets figure out a good way to handle this */
                        alert('Crop width exceeds original image width.');
                    }

                    return {
                        x1 : 0,
                        x2 : rectangleWidth,
                        y1 : 0,
                        y2 : rectangleHeight,
                        mode: 'constrain'
                    };
                },

                resize: function () {
                    var $img = this.$container.find('img'),
                        leftDistance = parseInt(this.$container.css('left'), 10),
                        topDistance = parseInt(this.$container.css('top'), 10);

                    var quotient = this.originalWidth / this.originalHeight,
                        leftAvailable = leftDistance - 10,
                        topAvailable = topDistance - 10;

                    if (leftAvailable / quotient > topAvailable) {
                        newWidth = this.$container.width() + (topAvailable * quotient);
                    } else {
                        newWidth = this.$container.width() + leftAvailable;
                    }

                    // Set the size so that the image always fits into a constraint x constraint box
                    newWidth = Math.min(newWidth, this.constraint, this.constraint * quotient, this.originalWidth);
                    this.$container.width(newWidth);

                    var factor = newWidth / this.originalWidth,
                        newHeight = this.originalHeight * factor;

                    $img.height(newHeight).width(newWidth);
                    //this.factor = factor;

                    if (typeof $img.imgAreaSelect({instance: true}) != "undefined") {
                        $img.imgAreaSelect({instance: true}).update();
                    }
                },

                saveImage: function() {
                    var selection = this.areaSelect.getSelection();
                    var coords= {
                        x1: Math.round(selection.x1 / this.factor),
                        x2: Math.round(selection.x2 / this.factor),
                        y1: Math.round(selection.y1 / this.factor),
                        y2: Math.round(selection.y2 / this.factor),
                    };

                    var data = $(this.$selectedItems).data();
                    var params = $.extend({}, coords, data, {name: name, entryId: entryId});

                    Craft.postActionRequest('imageCropper/saveManualCrop', params, $.proxy(function(response, textStatus) {
                        if (textStatus == 'success') {
                            if (response.error) {
                                Craft.cp.displayError(response.error);
                            } else {
                                $(this.$selectedItems).replaceWith(response.data);
                                Craft.cp.displayNotice(Craft.t(response.message));
                            }
                        }

                        this.onFadeOut();
                        this.$container.empty();
                    }, this));

                    this.removeListener(this.$saveBtn, 'click');
                    this.removeListener(this.$cancelBtn, 'click');

                    this.$container.find('.crop-image').fadeTo(50, 0.5);
                }

            });

        Craft.CropImageAreaTool = Garnish.Base.extend(
            {
                $container: null,

                init: function($container, settings) {
                    this.$container = $container;
                    this.setSettings(settings);
                },

                showArea: function(referenceObject) {
                    var $target = this.$container.find('img');
                    var areaOptions = {
                        aspectRatio: this.settings.aspectRatio,
                        maxWidth: $target.width(),
                        maxHeight: $target.height(),
                        instance: true,
                        resizable: false,
                        show: true,
                        persistent: true,
                        handles: true,
                        parent: $target.parent(),
                        classPrefix: 'imgareaselect'
                    };

                    var areaSelect = $target.imgAreaSelect(areaOptions);

                    var x1 = this.settings.initialRectangle.x1;
                    var x2 = this.settings.initialRectangle.x2;
                    var y1 = this.settings.initialRectangle.y1;
                    var y2 = this.settings.initialRectangle.y2;

                    areaSelect.setSelection(x1, y1, x2, y2);
                    areaSelect.update();

                    // Make sure we never go below 400px wide
                    referenceObject.desiredWidth = ($target.attr('width') <= 400) ? 400 : false;
                    referenceObject.desiredHeight = false;

                    referenceObject.areaSelect = areaSelect;
                    referenceObject.factor = $target.data('factor');
                    referenceObject.originalHeight = $target.attr('height');
                    referenceObject.originalWidth = $target.attr('width');
                    //referenceObject.originalHeight = $target.attr('height') / referenceObject.factor;
                    //referenceObject.originalWidth = $target.attr('width') / referenceObject.factor;
                    referenceObject.constraint = $target.data('constraint');
                    referenceObject.source = $target.attr('src').split('/').pop();
                    referenceObject.updateSizeAndPosition();
                }
            });

        $.extend($.imgAreaSelect.prototype, {
            animateSelection: function (x1, y1, x2, y2, duration) {
                var fx = $.extend($('<div/>')[0], {
                    ias: this,
                    start: this.getSelection(),
                    end: { x1: x1, y1: y1, x2: x2, y2: y2 }
                });

                $(fx).animate({
                        cur: 1
                    },
                    {
                        duration: duration,
                        step: function (now, fx) {
                            var start = fx.elem.start, end = fx.elem.end,
                                curX1 = Math.round(start.x1 + (end.x1 - start.x1) * now),
                                curY1 = Math.round(start.y1 + (end.y1 - start.y1) * now),
                                curX2 = Math.round(start.x2 + (end.x2 - start.x2) * now),
                                curY2 = Math.round(start.y2 + (end.y2 - start.y2) * now);
                            fx.elem.ias.setSelection(curX1, curY1, curX2, curY2);
                            fx.elem.ias.update();
                        }
                    });
            }
        });


        $('.imgCropper-crops').on('click', 'a.imgCropper-viewAsset', function() {
            var parent = $(this).parent();
            new Craft.CropImageModal(parent, parent, {});

        });

        $('.imgCropper-crops').on('click', 'a.view-image', function(){
            var parent = $(this).parent().parent();
            var url = parent.attr('data-url');
            var width = parent.attr('data-width');
            var height = parent.attr('data-height');
            var $container = $('' +
                '<div class="view-crop modal">' +
                '       <div class="crop-img">' +
                '           <img src="'+ url +'">' +
                '       </div>' +
                '</div>'
            );
            var settings = {
                desiredWidth: width,
                desiredHeight: height
            };
            var myModal = new Garnish.Modal($container, settings);
        });

    });