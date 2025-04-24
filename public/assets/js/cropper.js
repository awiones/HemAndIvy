// Fixed 4:3 aspect ratio cropper for auction image upload
// Filename: /assets/js/cropper.js

(function() {
    // Wait for DOM
    document.addEventListener('DOMContentLoaded', function() {
        // Get references to existing elements
        var dropArea = document.getElementById('image-drop-area');
        var input = document.getElementById('image-input');
        var preview = document.getElementById('image-preview');
        var img = preview.querySelector('img');
        var dropText = document.getElementById('drop-text');
        // Add reference to hidden base64 input
        var base64Input = document.getElementById('cropped-image-base64');

        // Create modal elements
        var modal = document.createElement('div');
        modal.style.cssText = 'display:none;position:fixed;z-index:9999;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.7);align-items:center;justify-content:center;';
        
        var cropBox = document.createElement('div');
        cropBox.style.cssText = 'background:#fff;padding:24px;border-radius:8px;box-shadow:0 4px 24px rgba(0,0,0,0.2);max-width:95vw;max-height:95vh;display:flex;flex-direction:column;align-items:center;';
        
        var cropperTitle = document.createElement('h3');
        cropperTitle.textContent = 'Crop Image (4:3)';
        cropperTitle.style.cssText = 'margin-top:0;margin-bottom:16px;color:#4B286D;font-family:\'Montserrat\',sans-serif;';
        
        var cropperInstructions = document.createElement('p');
        cropperInstructions.textContent = 'Drag to position or resize the crop area. Drag corners and edges to resize. The image will be resized to 400x300px.';
        cropperInstructions.style.cssText = 'margin-top:0;margin-bottom:16px;color:#666;font-size:14px;font-family:\'Montserrat\',sans-serif;';
        
        var cropContainer = document.createElement('div');
        cropContainer.style.cssText = 'position:relative;margin-bottom:16px;';
        
        var cropImgContainer = document.createElement('div');
        cropImgContainer.style.cssText = 'position:relative;overflow:hidden;';
        
        var cropImg = document.createElement('img');
        cropImg.style.cssText = 'max-width:60vw;max-height:60vh;display:block;';

        var controls = document.createElement('div');
        controls.style.cssText = 'display:flex;gap:1rem;justify-content:center;margin-top:16px;';

        var cropBtn = document.createElement('button');
        cropBtn.textContent = 'Crop & Use';
        cropBtn.style.cssText = 'padding:0.5rem 1.5rem;background:#4B286D;color:#fff;border:none;border-radius:4px;cursor:pointer;font-weight:600;font-family:\'Montserrat\',sans-serif;';

        var cancelBtn = document.createElement('button');
        cancelBtn.textContent = 'Cancel';
        cancelBtn.style.cssText = 'padding:0.5rem 1.5rem;background:#eee;color:#333;border:none;border-radius:4px;cursor:pointer;font-weight:600;font-family:\'Montserrat\',sans-serif;';

        controls.appendChild(cropBtn);
        controls.appendChild(cancelBtn);

        var canvas = document.createElement('canvas');
        canvas.style.cssText = 'position:absolute;top:0;left:0;pointer-events:none;z-index:2;';
        
        var cropOverlay = document.createElement('div');
        cropOverlay.style.cssText = 'position:absolute;top:0;left:0;right:0;bottom:0;pointer-events:auto;z-index:1;';
        
        cropImgContainer.appendChild(cropImg);
        cropImgContainer.appendChild(cropOverlay);
        cropImgContainer.appendChild(canvas);
        
        cropContainer.appendChild(cropImgContainer);
        
        cropBox.appendChild(cropperTitle);
        cropBox.appendChild(cropperInstructions);
        cropBox.appendChild(cropContainer);
        cropBox.appendChild(controls);
        modal.appendChild(cropBox);
        document.body.appendChild(modal);

        var cropData = null;
        var dragging = false;
        var resizing = false;
        var resizeHandle = null;
        var startX = 0, startY = 0;
        var cropRect = null;
        var imgRatio = 1;
        var aspectW = 4, aspectH = 3;
        var handleSize = 10; // Size of resize handles in pixels
        var ignoreNextInputChange = false; // Add this flag

        // Create resize handles
        var handles = [];
        var handlePositions = [
            'nw', 'n', 'ne',
            'w', 'e',
            'sw', 's', 'se'
        ];

        function drawCropRect() {
            if (!canvas || !cropRect) return;
            
            // Ensure canvas matches overlay/image size
            canvas.width = cropImg.clientWidth;
            canvas.height = cropImg.clientHeight;

            var ctx = canvas.getContext('2d');
            if (!ctx) return;
            
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            ctx.fillStyle = 'rgba(0, 0, 0, 0.5)';
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            // Clear the crop rectangle area
            ctx.clearRect(cropRect.x, cropRect.y, cropRect.w, cropRect.h);

            // Draw border
            ctx.save();
            ctx.strokeStyle = '#4B286D';
            ctx.lineWidth = 2;
            ctx.strokeRect(cropRect.x, cropRect.y, cropRect.w, cropRect.h);

            // Draw grid lines
            ctx.strokeStyle = 'rgba(255, 255, 255, 0.8)';
            ctx.lineWidth = 1;
            ctx.beginPath();
            
            // Vertical lines
            ctx.moveTo(cropRect.x + cropRect.w/3, cropRect.y);
            ctx.lineTo(cropRect.x + cropRect.w/3, cropRect.y + cropRect.h);
            ctx.moveTo(cropRect.x + cropRect.w*2/3, cropRect.y);
            ctx.lineTo(cropRect.x + cropRect.w*2/3, cropRect.y + cropRect.h);
            
            // Horizontal lines
            ctx.moveTo(cropRect.x, cropRect.y + cropRect.h/3);
            ctx.lineTo(cropRect.x + cropRect.w, cropRect.y + cropRect.h/3);
            ctx.moveTo(cropRect.x, cropRect.y + cropRect.h*2/3);
            ctx.lineTo(cropRect.x + cropRect.w, cropRect.y + cropRect.h*2/3);
            
            ctx.stroke();
            
            // Draw resize handles
            ctx.fillStyle = '#fff';
            ctx.strokeStyle = '#4B286D';
            ctx.lineWidth = 1;
            
            // Draw handles at corners and edges
            // Northwest (top-left)
            ctx.beginPath();
            ctx.arc(cropRect.x, cropRect.y, handleSize/2, 0, Math.PI * 2);
            ctx.fill();
            ctx.stroke();
            
            // North (top-center)
            ctx.beginPath();
            ctx.arc(cropRect.x + cropRect.w/2, cropRect.y, handleSize/2, 0, Math.PI * 2);
            ctx.fill();
            ctx.stroke();
            
            // Northeast (top-right)
            ctx.beginPath();
            ctx.arc(cropRect.x + cropRect.w, cropRect.y, handleSize/2, 0, Math.PI * 2);
            ctx.fill();
            ctx.stroke();
            
            // West (middle-left)
            ctx.beginPath();
            ctx.arc(cropRect.x, cropRect.y + cropRect.h/2, handleSize/2, 0, Math.PI * 2);
            ctx.fill();
            ctx.stroke();
            
            // East (middle-right)
            ctx.beginPath();
            ctx.arc(cropRect.x + cropRect.w, cropRect.y + cropRect.h/2, handleSize/2, 0, Math.PI * 2);
            ctx.fill();
            ctx.stroke();
            
            // Southwest (bottom-left)
            ctx.beginPath();
            ctx.arc(cropRect.x, cropRect.y + cropRect.h, handleSize/2, 0, Math.PI * 2);
            ctx.fill();
            ctx.stroke();
            
            // South (bottom-center)
            ctx.beginPath();
            ctx.arc(cropRect.x + cropRect.w/2, cropRect.y + cropRect.h, handleSize/2, 0, Math.PI * 2);
            ctx.fill();
            ctx.stroke();
            
            // Southeast (bottom-right)
            ctx.beginPath();
            ctx.arc(cropRect.x + cropRect.w, cropRect.y + cropRect.h, handleSize/2, 0, Math.PI * 2);
            ctx.fill();
            ctx.stroke();
            
            ctx.restore();
        }

        function showCropper(file) {
            if (!file) {
                console.error("No file provided to cropper");
                return;
            }
            
            var reader = new FileReader();
            reader.onload = function(e) {
                // Preload the image to get dimensions
                var tempImg = new Image();
                tempImg.onload = function() {
                    cropImg.src = e.target.result;
                    
                    // Wait for the image to be loaded and rendered in the DOM
                    cropImg.onload = function() {
                        modal.style.display = 'flex';
                        
                        // Need to wait a moment for the browser to render
                        setTimeout(function() {
                            initializeCropArea();
                        }, 100);
                    };
                };
                tempImg.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
        
        function initializeCropArea() {
            // Set canvas and overlay size to match displayed image
            var width = cropImg.clientWidth;
            var height = cropImg.clientHeight;
            
            if (width <= 0 || height <= 0) {
                console.error("Image dimensions are invalid", width, height);
                return;
            }
            
            // Update canvas dimensions
            canvas.width = width;
            canvas.height = height;
            
            // Update overlay dimensions
            cropOverlay.style.width = width + 'px';
            cropOverlay.style.height = height + 'px';

            // Calculate image scale ratio
            imgRatio = cropImg.naturalWidth / width;

            // Calculate crop rectangle dimensions
            var cropW, cropH;
            if (width / height > aspectW / aspectH) {
                // Image is wider than our target aspect ratio
                cropH = Math.min(height, height * 0.9);
                cropW = cropH * aspectW / aspectH;
            } else {
                // Image is taller than our target aspect ratio
                cropW = Math.min(width, width * 0.9);
                cropH = cropW * aspectH / aspectW;
            }

            // Center the crop rectangle
            var x = Math.max(0, (width - cropW) / 2);
            var y = Math.max(0, (height - cropH) / 2);

            // Create crop rectangle
            cropRect = {
                x: x,
                y: y,
                w: cropW,
                h: cropH
            };

            // Draw the crop overlay
            drawCropRect();
        }
        
        // Function to check if point is over a resize handle
        function getResizeHandle(x, y) {
            if (!cropRect) return null;
            
            var handlePositions = [
                // [x, y, cursor, name]
                [cropRect.x, cropRect.y, 'nw-resize', 'nw'],
                [cropRect.x + cropRect.w/2, cropRect.y, 'n-resize', 'n'],
                [cropRect.x + cropRect.w, cropRect.y, 'ne-resize', 'ne'],
                [cropRect.x, cropRect.y + cropRect.h/2, 'w-resize', 'w'],
                [cropRect.x + cropRect.w, cropRect.y + cropRect.h/2, 'e-resize', 'e'],
                [cropRect.x, cropRect.y + cropRect.h, 'sw-resize', 'sw'],
                [cropRect.x + cropRect.w/2, cropRect.y + cropRect.h, 's-resize', 's'],
                [cropRect.x + cropRect.w, cropRect.y + cropRect.h, 'se-resize', 'se']
            ];
            
            for (var i = 0; i < handlePositions.length; i++) {
                var pos = handlePositions[i];
                var dx = x - pos[0];
                var dy = y - pos[1];
                
                if (Math.sqrt(dx*dx + dy*dy) <= handleSize/2) {
                    cropOverlay.style.cursor = pos[2];
                    return pos[3];
                }
            }
            
            // Check if inside crop rect (for moving)
            if (x >= cropRect.x && x <= cropRect.x + cropRect.w &&
                y >= cropRect.y && y <= cropRect.y + cropRect.h) {
                cropOverlay.style.cursor = 'move';
                return 'move';
            }
            
            cropOverlay.style.cursor = 'default';
            return null;
        }
        
        // Move handler for resize
        function resizeRect(handle, dx, dy) {
            var origX = cropRect.x;
            var origY = cropRect.y;
            var origW = cropRect.w;
            var origH = cropRect.h;
            var newX = origX;
            var newY = origY;
            var newW = origW;
            var newH = origH;
            
            // Calculate aspect ratio
            var aspect = aspectW / aspectH;
            
            // Handle resize based on which handle was dragged
            switch (handle) {
                case 'nw': // Top-left
                    newW = origW - dx;
                    newH = newW / aspect;
                    newX = origX + origW - newW;
                    newY = origY + origH - newH;
                    break;
                    
                case 'n': // Top-center
                    newH = origH - dy;
                    newW = newH * aspect;
                    newX = origX + (origW - newW) / 2;
                    newY = origY + origH - newH;
                    break;
                    
                case 'ne': // Top-right
                    newW = origW + dx;
                    newH = newW / aspect;
                    newX = origX;
                    newY = origY + origH - newH;
                    break;
                    
                case 'w': // Middle-left
                    newW = origW - dx;
                    newH = newW / aspect;
                    newX = origX + origW - newW;
                    newY = origY + (origH - newH) / 2;
                    break;
                    
                case 'e': // Middle-right
                    newW = origW + dx;
                    newH = newW / aspect;
                    newX = origX;
                    newY = origY + (origH - newH) / 2;
                    break;
                    
                case 'sw': // Bottom-left
                    newW = origW - dx;
                    newH = newW / aspect;
                    newX = origX + origW - newW;
                    newY = origY;
                    break;
                    
                case 's': // Bottom-center
                    newH = origH + dy;
                    newW = newH * aspect;
                    newX = origX + (origW - newW) / 2;
                    newY = origY;
                    break;
                    
                case 'se': // Bottom-right
                    newW = origW + dx;
                    newH = newW / aspect;
                    break;
            }
            
            // Ensure minimum size
            var minSize = 50;
            if (newW < minSize) {
                newW = minSize;
                newH = minSize / aspect;
            }
            
            // Ensure crop area stays within bounds
            var maxW = canvas.width - newX;
            var maxH = canvas.height - newY;
            
            if (newW > maxW) {
                newW = maxW;
                newH = newW / aspect;
            }
            
            if (newH > maxH) {
                newH = maxH;
                newW = newH * aspect;
            }
            
            if (newX < 0) {
                newX = 0;
                if (handle === 'nw' || handle === 'w' || handle === 'sw') {
                    newW = origX + origW;
                    newH = newW / aspect;
                }
            }
            
            if (newY < 0) {
                newY = 0;
                if (handle === 'nw' || handle === 'n' || handle === 'ne') {
                    newH = origY + origH;
                    newW = newH * aspect;
                }
            }
            
            cropRect.x = newX;
            cropRect.y = newY;
            cropRect.w = newW;
            cropRect.h = newH;
            
            drawCropRect();
        }

        // Mouse events for dragging and resizing
        cropOverlay.addEventListener('mousemove', function(e) {
            if (!cropRect) return;
            
            var rect = cropImg.getBoundingClientRect();
            var x = e.clientX - rect.left;
            var y = e.clientY - rect.top;
            
            if (!dragging && !resizing) {
                getResizeHandle(x, y);
            }
        });
        
        cropOverlay.addEventListener('mousedown', function(e) {
            handleMouseDown(e);
        });
        
        cropImg.addEventListener('mousedown', function(e) {
            handleMouseDown(e);
        });
        
        function handleMouseDown(e) {
            if (!cropRect) return;
            
            var rect = cropImg.getBoundingClientRect();
            var x = e.clientX - rect.left;
            var y = e.clientY - rect.top;
            
            var handle = getResizeHandle(x, y);
            
            if (handle === 'move') {
                dragging = true;
                startX = e.clientX;
                startY = e.clientY;
                e.preventDefault();
            } else if (handle) {
                resizing = true;
                resizeHandle = handle;
                startX = e.clientX;
                startY = e.clientY;
                e.preventDefault();
            }
        }
        
        window.addEventListener('mousemove', function(e) {
            if (dragging && cropRect) {
                var dx = e.clientX - startX;
                var dy = e.clientY - startY;
                
                cropRect.x = Math.max(0, Math.min(cropRect.x + dx, canvas.width - cropRect.w));
                cropRect.y = Math.max(0, Math.min(cropRect.y + dy, canvas.height - cropRect.h));
                
                startX = e.clientX;
                startY = e.clientY;
                
                drawCropRect();
                e.preventDefault();
            } else if (resizing && cropRect && resizeHandle) {
                var dx = e.clientX - startX;
                var dy = e.clientY - startY;
                
                resizeRect(resizeHandle, dx, dy);
                
                startX = e.clientX;
                startY = e.clientY;
                
                e.preventDefault();
            }
        });
        
        window.addEventListener('mouseup', function() {
            dragging = false;
            resizing = false;
            resizeHandle = null;
        });

        // Touch events for mobile devices
        cropOverlay.addEventListener('touchstart', function(e) {
            handleTouchStart(e);
        });
        
        cropImg.addEventListener('touchstart', function(e) {
            handleTouchStart(e);
        });
        
        function handleTouchStart(e) {
            if (!cropRect || e.touches.length !== 1) return;
            
            var touch = e.touches[0];
            var rect = cropImg.getBoundingClientRect();
            var x = touch.clientX - rect.left;
            var y = touch.clientY - rect.top;
            
            var handle = getResizeHandle(x, y);
            
            if (handle === 'move') {
                dragging = true;
                startX = touch.clientX;
                startY = touch.clientY;
                e.preventDefault();
            } else if (handle) {
                resizing = true;
                resizeHandle = handle;
                startX = touch.clientX;
                startY = touch.clientY;
                e.preventDefault();
            }
        }
        
        window.addEventListener('touchmove', function(e) {
            if (e.touches.length !== 1) return;
            
            var touch = e.touches[0];
            
            if (dragging && cropRect) {
                var dx = touch.clientX - startX;
                var dy = touch.clientY - startY;
                
                cropRect.x = Math.max(0, Math.min(cropRect.x + dx, canvas.width - cropRect.w));
                cropRect.y = Math.max(0, Math.min(cropRect.y + dy, canvas.height - cropRect.h));
                
                startX = touch.clientX;
                startY = touch.clientY;
                
                drawCropRect();
                e.preventDefault();
            } else if (resizing && cropRect && resizeHandle) {
                var dx = touch.clientX - startX;
                var dy = touch.clientY - startY;
                
                resizeRect(resizeHandle, dx, dy);
                
                startX = touch.clientX;
                startY = touch.clientY;
                
                e.preventDefault();
            }
        });
        
        window.addEventListener('touchend', function() {
            dragging = false;
            resizing = false;
            resizeHandle = null;
        });

        cropBtn.addEventListener('click', function() {
            if (!cropRect || !cropImg || !cropImg.complete || cropImg.naturalWidth === 0) {
                alert('Image not loaded properly. Please try again.');
                return;
            }
            
            if (cropRect.w <= 0 || cropRect.h <= 0) {
                alert('Invalid crop area. Please try again.');
                return;
            }

            // Calculate the actual crop dimensions in the original image
            var actualCropX = cropRect.x * imgRatio;
            var actualCropY = cropRect.y * imgRatio;
            var actualCropW = cropRect.w * imgRatio;
            var actualCropH = cropRect.h * imgRatio;

            // Ensure values are within bounds
            actualCropX = Math.max(0, Math.min(actualCropX, cropImg.naturalWidth - 1));
            actualCropY = Math.max(0, Math.min(actualCropY, cropImg.naturalHeight - 1));
            actualCropW = Math.max(1, Math.min(actualCropW, cropImg.naturalWidth - actualCropX));
            actualCropH = Math.max(1, Math.min(actualCropH, cropImg.naturalHeight - actualCropY));

            // Create a canvas for cropping
            var cropCanvas = document.createElement('canvas');
            cropCanvas.width = 400;
            cropCanvas.height = 300;
            var ctx = cropCanvas.getContext('2d');
            
            try {
                // Draw the cropped image onto the canvas
                ctx.clearRect(0, 0, 400, 300);
                ctx.drawImage(
                    cropImg,
                    actualCropX, actualCropY, actualCropW, actualCropH,
                    0, 0, 400, 300
                );

                // Convert to data URL
                var dataUrl = cropCanvas.toDataURL('image/png');
                if (dataUrl && dataUrl !== 'data:,') {
                    img.src = dataUrl;
                    preview.style.display = 'block';
                    dropText.style.display = 'none';
                    // Always set the hidden input for base64
                    if (base64Input) base64Input.value = dataUrl;
                } else {
                    alert('Failed to crop image. Please try again.');
                    return;
                }

                // Convert canvas to Blob
                cropCanvas.toBlob(function(blob) {
                    if (!blob) {
                        alert('Failed to process image. Please try again.');
                        return;
                    }
                    
                    // Create a File object from the Blob
                    var croppedFile = new File([blob], "cropped.png", {type: "image/png"});

                    try {
                        // Use DataTransfer to update the file input
                        var dt = new DataTransfer();
                        dt.items.add(croppedFile);
                        input.files = dt.files;

                        // Set flag to ignore next change event
                        ignoreNextInputChange = true;

                        // Dispatch change event
                        var event = new Event('change', { bubbles: true });
                        input.dispatchEvent(event);
                    } catch (e) {
                        console.warn('DataTransfer not supported, using direct assignment:', e);
                    }

                    // Clear the file input value to prevent re-triggering cropper
                    input.value = '';
                }, 'image/png');

                // Hide the modal
                modal.style.display = 'none';
            } catch (e) {
                console.error("Error during image crop:", e);
                alert('An error occurred during cropping. Please try again.');
            }
        });

        cancelBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });

        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.style.display === 'flex') {
                modal.style.display = 'none';
            }
        });

        function handleFile(file) {
            if (!file) {
                console.error("No file provided");
                return;
            }
            
            if (!file.type.match(/^image\//)) {
                alert('Please select a valid image file.');
                return;
            }
            
            showCropper(file);
        }
        
        if (input) {
            input.addEventListener('change', function() {
                if (ignoreNextInputChange) {
                    ignoreNextInputChange = false;
                    return; // Prevent cropper from reopening for cropped image
                }
                if (input.files && input.files[0]) {
                    handleFile(input.files[0]);
                }
            });
        }
        
        if (dropArea) {
            dropArea.addEventListener('dragover', function(e) {
                e.preventDefault();
                dropArea.style.borderColor = '#4B286D';
                dropArea.style.backgroundColor = '#f0ebf5';
            });
            
            dropArea.addEventListener('dragleave', function() {
                dropArea.style.borderColor = '';
                dropArea.style.backgroundColor = '';
            });
            
            dropArea.addEventListener('drop', function(e) {
                e.preventDefault();
                dropArea.style.borderColor = '';
                dropArea.style.backgroundColor = '';
                
                if (e.dataTransfer.files && e.dataTransfer.files[0]) {
                    handleFile(e.dataTransfer.files[0]);
                }
            });
            
            dropArea.addEventListener('click', function() {
                input.click();
            });
        }
    });
})();