import { Component, OnInit, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { AssetService } from '../../services/asset.service';
import { Asset } from '../../models/asset.model';
import { Subject } from 'rxjs';
import { debounceTime, distinctUntilChanged } from 'rxjs/operators';

@Component({
    selector: 'app-etiquetas-qr',
    standalone: true,
    imports: [CommonModule, FormsModule],
    templateUrl: './etiquetas-qr.component.html',
    styles: [`
        @media print {
            .no-print { display: none !important; }
            .print-only { display: block !important; }
            .qr-grid { 
                display: grid !important; 
                grid-template-columns: repeat(3, 1fr) !important;
                gap: 10px !important;
            }
            .qr-card {
                border: 1px solid #000 !important;
                padding: 10px !important;
                page-break-inside: avoid;
            }
        }
    `]
})
export class EtiquetasQrComponent implements OnInit {
    assets: Asset[] = [];
    selectedAssets: Asset[] = [];
    loading = false;
    searchTerm = '';
    searchUpdate = new Subject<string>();

    // Map to store temporary object URLs for QR codes
    qrUrls: Map<number, string> = new Map();

    constructor(
        private assetService: AssetService,
        private cdr: ChangeDetectorRef
    ) {
        this.searchUpdate.pipe(
            debounceTime(400),
            distinctUntilChanged()
        ).subscribe(value => {
            if (value.length > 2) {
                this.searchAssets(value);
            } else {
                this.assets = [];
            }
        });
    }

    ngOnInit() { }

    searchAssets(term: string) {
        this.loading = true;
        this.assetService.getAssets(1, term).subscribe({
            next: (res) => {
                this.assets = res.data;
                this.loading = false;
                this.cdr.detectChanges();
            },
            error: () => {
                this.loading = false;
                this.cdr.detectChanges();
            }
        });
    }

    toggleSelection(asset: Asset) {
        const index = this.selectedAssets.findIndex(a => a.id === asset.id);
        if (index > -1) {
            this.selectedAssets.splice(index, 1);
            // Clean up URL if removing
            if (this.qrUrls.has(asset.id)) {
                URL.revokeObjectURL(this.qrUrls.get(asset.id)!);
                this.qrUrls.delete(asset.id);
            }
        } else {
            this.selectedAssets.push(asset);
            this.loadQrCode(asset.id);
        }
    }

    loadQrCode(id: number) {
        this.assetService.getQrBlob(id).subscribe({
            next: (blob) => {
                const url = URL.createObjectURL(blob);
                this.qrUrls.set(id, url);
                this.cdr.detectChanges();
            },
            error: (err) => console.error('Error loading QR', err)
        });
    }

    isSelected(asset: Asset): boolean {
        return this.selectedAssets.some(a => a.id === asset.id);
    }

    printLabels() {
        window.print();
    }

    clearSelection() {
        // Cleanup all URLs
        this.selectedAssets.forEach(asset => {
            if (this.qrUrls.has(asset.id)) {
                URL.revokeObjectURL(this.qrUrls.get(asset.id)!);
            }
        });
        this.qrUrls.clear();
        this.selectedAssets = [];
    }

    getQrUrl(id: number): string {
        return this.qrUrls.get(id) || 'assets/images/loading-qr.png'; // Fallback or placeholder
    }
}
