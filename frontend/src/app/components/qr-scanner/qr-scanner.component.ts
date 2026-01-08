import { Component, EventEmitter, Output, ViewChild } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ZXingScannerModule, ZXingScannerComponent } from '@zxing/ngx-scanner';
import { BarcodeFormat } from '@zxing/library';

@Component({
    selector: 'app-qr-scanner',
    standalone: true,
    imports: [CommonModule, ZXingScannerModule],
    templateUrl: './qr-scanner.component.html',
    styleUrls: ['./qr-scanner.component.css']
})
export class QrScannerComponent {
    @Output() scanSuccess = new EventEmitter<string>();
    @Output() scanError = new EventEmitter<any>();
    @Output() close = new EventEmitter<void>();

    @ViewChild('scanner') scanner!: ZXingScannerComponent;

    allowedFormats = [BarcodeFormat.QR_CODE, BarcodeFormat.CODE_128, BarcodeFormat.EAN_13];
    hasDevices = false;
    hasPermission = false;
    qrResultString = '';
    enabled = true;

    onCodeResult(resultString: string) {
        this.qrResultString = resultString;
        this.scanSuccess.emit(resultString);
        this.enabled = false; // Stop scanning after success
    }

    onHasPermission(has: boolean) {
        this.hasPermission = has;
    }

    onCamerasFound(devices: MediaDeviceInfo[]) {
        this.hasDevices = devices && devices.length > 0;
    }

    onCamerasNotFound() {
        this.hasDevices = false;
    }

    onScanError(error: any) {
        this.scanError.emit(error);
    }

    toggleScanner() {
        this.enabled = !this.enabled;
    }

    onClose() {
        this.enabled = false;
        this.close.emit();
    }
}
