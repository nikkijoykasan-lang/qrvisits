declare class BarcodeDetector {
    constructor(options?: { formats?: string[] });
    static getSupportedFormats(): Promise<string[]>;
    detect(
        source: HTMLVideoElement,
    ): Promise<{ rawValue: string; format: string }[]>;
}

interface Window {
    BarcodeDetector?: typeof BarcodeDetector;
}
