import { Component, OnInit, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { MainLayoutComponent } from '../../layouts/main-layout/main-layout.component';
import { AssetService } from '../../services/asset.service';
import { Asset } from '../../models/asset.model';
import { Subject } from 'rxjs';
import { debounceTime, distinctUntilChanged } from 'rxjs/operators';

@Component({
    selector: 'app-etiquetas-qr',
    standalone: true,
    imports: [CommonModule, FormsModule, MainLayoutComponent],
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
        } else {
            this.selectedAssets.push(asset);
        }
    }

    isSelected(asset: Asset): boolean {
        return this.selectedAssets.some(a => a.id === asset.id);
    }

    printLabels() {
        window.print();
    }

    clearSelection() {
        this.selectedAssets = [];
    }

    generateQrUrl(code: string): string {
        return `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${code}`;
    }
}
