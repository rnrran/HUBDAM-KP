<template>
  <div class="image-cropper">
    <!-- Cropper Modal -->
    <Dialog v-model:open="showCropper" class="w-full max-w-4xl">
      <DialogContent class="max-w-4xl">
        <DialogHeader>
          <DialogTitle>Crop Gambar Profile</DialogTitle>
          <DialogDescription>
            Atur dan crop gambar profile Anda sesuai keinginan
          </DialogDescription>
        </DialogHeader>
        
        <div class="space-y-4">
          <!-- Cropper Container -->
          <div class="w-full h-96 border rounded-lg overflow-hidden bg-gray-100 relative">
            <canvas
              v-if="imageSrc"
              ref="canvasRef"
              class="max-w-full max-h-full mx-auto block"
              @mousedown="startDrag"
              @mousemove="onDrag"
              @mouseup="stopDrag"
              @wheel="onWheel"
            ></canvas>
            <div v-else class="w-full h-full flex items-center justify-center">
              <p class="text-gray-500">Loading gambar...</p>
            </div>
            
            <!-- Crop Overlay -->
            <div
              v-if="imageSrc"
              class="absolute border-2 border-white border-dashed pointer-events-none"
              :style="cropBoxStyle"
            ></div>
          </div>
          
          <!-- Controls -->
          <div class="flex flex-wrap gap-2 justify-center">
            <Button 
              variant="outline" 
              size="sm" 
              @click="rotateLeft"
              :disabled="!imageSrc"
            >
              Putar Kiri
            </Button>
            <Button 
              variant="outline" 
              size="sm" 
              @click="rotateRight"
              :disabled="!imageSrc"
            >
              Putar Kanan
            </Button>
            <Button 
              variant="outline" 
              size="sm" 
              @click="zoomIn"
              :disabled="!imageSrc"
            >
              Perbesar
            </Button>
            <Button 
              variant="outline" 
              size="sm" 
              @click="zoomOut"
              :disabled="!imageSrc"
            >
              Perkecil
            </Button>
            <Button 
              variant="outline" 
              size="sm" 
              @click="reset"
              :disabled="!imageSrc"
            >
              Reset
            </Button>
          </div>
        </div>
        
        <DialogFooter>
          <Button variant="outline" @click="cancelCrop">
            Batal
          </Button>
          <Button @click="cropImage" :disabled="!imageSrc || cropping">
            <LoaderCircle v-if="cropping" class="h-4 w-4 animate-spin mr-2" />
            Crop & Simpan
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, nextTick, watch, onMounted, computed } from 'vue';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { LoaderCircle } from 'lucide-vue-next';

interface Props {
  modelValue: boolean;
  imageFile: File | null;
}

interface Emits {
  (e: 'update:modelValue', value: boolean): void;
  (e: 'cropped', file: File): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const showCropper = ref(false);
const imageSrc = ref<string>('');
const canvasRef = ref<HTMLCanvasElement>();
const cropping = ref(false);

// Canvas state
const image = ref<HTMLImageElement>();
const scale = ref(1);
const rotation = ref(0);
const offsetX = ref(0);
const offsetY = ref(0);
const isDragging = ref(false);
const lastX = ref(0);
const lastY = ref(0);

// Crop box
const cropBoxSize = ref(200);
const cropBoxX = ref(0);
const cropBoxY = ref(0);

const cropBoxStyle = computed(() => ({
  left: `${cropBoxX.value}px`,
  top: `${cropBoxY.value}px`,
  width: `${cropBoxSize.value}px`,
  height: `${cropBoxSize.value}px`,
}));

// Watch for modelValue changes
watch(() => props.modelValue, async (newValue) => {
  showCropper.value = newValue;
  if (newValue && props.imageFile) {
    await nextTick();
    loadImage();
  }
});

// Watch for showCropper changes
watch(showCropper, (newValue) => {
  emit('update:modelValue', newValue);
  if (!newValue) {
    resetCropper();
  }
});

const loadImage = async () => {
  if (props.imageFile) {
    console.log('Loading image:', props.imageFile.name, props.imageFile.size);
    const reader = new FileReader();
    reader.onload = async (e) => {
      console.log('Image loaded, result length:', e.target?.result?.toString().length);
      imageSrc.value = e.target?.result as string;
      
      // Create image element
      const img = new Image();
      img.onload = async () => {
        image.value = img;
        await nextTick();
        drawImage();
        centerCropBox();
        console.log('Image loaded and drawn');
      };
      img.src = imageSrc.value;
    };
    reader.onerror = (error) => {
      console.error('Error reading file:', error);
    };
    reader.readAsDataURL(props.imageFile);
  } else {
    console.log('No image file provided');
  }
};

const drawImage = () => {
  if (!canvasRef.value || !image.value) return;
  
  const canvas = canvasRef.value;
  const ctx = canvas.getContext('2d');
  if (!ctx) return;
  
  // Set canvas size
  canvas.width = 400;
  canvas.height = 400;
  
  // Clear canvas
  ctx.clearRect(0, 0, canvas.width, canvas.height);
  
  // Save context
  ctx.save();
  
  // Move to center
  ctx.translate(canvas.width / 2, canvas.height / 2);
  
  // Apply transformations
  ctx.rotate((rotation.value * Math.PI) / 180);
  ctx.scale(scale.value, scale.value);
  
  // Draw image
  const imgWidth = image.value.width;
  const imgHeight = image.value.height;
  const size = Math.min(imgWidth, imgHeight);
  
  ctx.drawImage(
    image.value,
    (imgWidth - size) / 2,
    (imgHeight - size) / 2,
    size,
    size,
    -size / 2,
    -size / 2,
    size,
    size
  );
  
  // Restore context
  ctx.restore();
};

const centerCropBox = () => {
  if (!canvasRef.value) return;
  cropBoxX.value = (canvasRef.value.width - cropBoxSize.value) / 2;
  cropBoxY.value = (canvasRef.value.height - cropBoxSize.value) / 2;
};

const startDrag = (e: MouseEvent) => {
  isDragging.value = true;
  lastX.value = e.clientX;
  lastY.value = e.clientY;
};

const onDrag = (e: MouseEvent) => {
  if (!isDragging.value) return;
  
  const deltaX = e.clientX - lastX.value;
  const deltaY = e.clientY - lastY.value;
  
  cropBoxX.value += deltaX;
  cropBoxY.value += deltaY;
  
  // Keep crop box within canvas bounds
  if (!canvasRef.value) return;
  cropBoxX.value = Math.max(0, Math.min(canvasRef.value.width - cropBoxSize.value, cropBoxX.value));
  cropBoxY.value = Math.max(0, Math.min(canvasRef.value.height - cropBoxSize.value, cropBoxY.value));
  
  lastX.value = e.clientX;
  lastY.value = e.clientY;
};

const stopDrag = () => {
  isDragging.value = false;
};

const onWheel = (e: WheelEvent) => {
  e.preventDefault();
  const delta = e.deltaY > 0 ? 0.9 : 1.1;
  scale.value = Math.max(0.1, Math.min(3, scale.value * delta));
  drawImage();
};

const rotateLeft = () => {
  rotation.value -= 90;
  drawImage();
};

const rotateRight = () => {
  rotation.value += 90;
  drawImage();
};

const zoomIn = () => {
  scale.value = Math.min(3, scale.value * 1.1);
  drawImage();
};

const zoomOut = () => {
  scale.value = Math.max(0.1, scale.value * 0.9);
  drawImage();
};

const reset = () => {
  scale.value = 1;
  rotation.value = 0;
  offsetX.value = 0;
  offsetY.value = 0;
  centerCropBox();
  drawImage();
};

const cropImage = async () => {
  if (!canvasRef.value || !image.value) return;
  
  cropping.value = true;
  try {
    const canvas = canvasRef.value;
    const ctx = canvas.getContext('2d');
    if (!ctx) return;
    
    // Create new canvas for cropped image
    const croppedCanvas = document.createElement('canvas');
    const croppedCtx = croppedCanvas.getContext('2d');
    if (!croppedCtx) return;
    
    croppedCanvas.width = cropBoxSize.value;
    croppedCanvas.height = cropBoxSize.value;
    
    // Draw cropped portion
    croppedCtx.drawImage(
      canvas,
      cropBoxX.value,
      cropBoxY.value,
      cropBoxSize.value,
      cropBoxSize.value,
      0,
      0,
      cropBoxSize.value,
      cropBoxSize.value
    );
    
    // Convert to blob
    const blob = await new Promise<Blob>((resolve, reject) => {
      croppedCanvas.toBlob((blob) => {
        if (blob) {
          resolve(blob);
        } else {
          reject(new Error('Failed to create blob'));
        }
      }, 'image/jpeg', 0.9);
    });
    
    // Create new file from cropped blob
    const croppedFile = new File([blob], props.imageFile?.name || 'cropped-image.jpg', {
      type: 'image/jpeg',
      lastModified: Date.now(),
    });
    
    emit('cropped', croppedFile);
    showCropper.value = false;
  } catch (error) {
    console.error('Error cropping image:', error);
  } finally {
    cropping.value = false;
  }
};

const cancelCrop = () => {
  showCropper.value = false;
  resetCropper();
};

const resetCropper = () => {
  imageSrc.value = '';
  image.value = undefined;
  scale.value = 1;
  rotation.value = 0;
  offsetX.value = 0;
  offsetY.value = 0;
  if (canvasRef.value) {
    const ctx = canvasRef.value.getContext('2d');
    if (ctx) {
      ctx.clearRect(0, 0, canvasRef.value.width, canvasRef.value.height);
    }
  }
};
</script>

<style scoped>
.image-cropper {
  width: 100%;
}

canvas {
  cursor: move;
}
</style>
