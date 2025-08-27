declare module 'vue-cropper' {
  import { DefineComponent } from 'vue';
  
  interface CropperOptions {
    src: string;
    aspectRatio?: number;
    viewMode?: number;
    autoCropArea?: number;
    background?: boolean;
    movable?: boolean;
    zoomable?: boolean;
    scalable?: boolean;
    rotatable?: boolean;
    center?: boolean;
    highlight?: boolean;
    cropBoxMovable?: boolean;
    cropBoxResizable?: boolean;
    toggleDragModeOnDblclick?: boolean;
  }
  
  interface CropperInstance {
    rotate(degree: number): void;
    zoom(ratio: number): void;
    reset(): void;
    refresh(): void;
    getCanvas(): HTMLCanvasElement;
  }
  
  const VueCropper: DefineComponent<CropperOptions, {}, {}, {}, {}, {}, {}, {}, string, {}, {}, {}>;
  
  export default VueCropper;
}
