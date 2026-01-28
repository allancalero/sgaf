import { Component, Input, forwardRef } from '@angular/core';
import { ControlValueAccessor, NG_VALUE_ACCESSOR, ReactiveFormsModule, FormControl } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { FontAwesomeModule } from '@fortawesome/angular-fontawesome';
import { faEye, faEyeSlash } from '@fortawesome/free-solid-svg-icons';

@Component({
    selector: 'app-password-input',
    standalone: true,
    imports: [CommonModule, ReactiveFormsModule, FontAwesomeModule],
    templateUrl: './password-input.component.html',
    styleUrls: ['./password-input.component.css'],
    providers: [
        {
            provide: NG_VALUE_ACCESSOR,
            useExisting: forwardRef(() => PasswordInputComponent),
            multi: true
        }
    ]
})
export class PasswordInputComponent implements ControlValueAccessor {
    @Input() label: string = 'Contraseña';
    @Input() placeholder: string = 'Ingrese su contraseña';
    @Input() showValidation: boolean = false;
    @Input() inputClass: string = 'form-control'; // Default to bootstrap class if not provided

    // Icons
    faEye = faEye;
    faEyeSlash = faEyeSlash;

    // Internal form control to bind with
    control: FormControl = new FormControl('');

    // State
    isVisible: boolean = false;

    // Validation State
    hasMinLength: boolean = false;
    hasUpperCase: boolean = false;
    hasNumber: boolean = false;
    hasSpecialChar: boolean = false;

    // ControlValueAccessor callbacks
    onChange: any = () => { };
    onTouch: any = () => { };

    toggleVisibility() {
        this.isVisible = !this.isVisible;
    }

    // Handle input changes
    onInput(event: any) {
        const value = event.target.value;
        this.control.setValue(value);
        this.updateValidation(value);
        this.onChange(value);
    }

    updateValidation(value: string) {
        if (!value) {
            this.hasMinLength = false;
            this.hasUpperCase = false;
            this.hasNumber = false;
            this.hasSpecialChar = false;
            return;
        }

        this.hasMinLength = value.length >= 8;
        this.hasUpperCase = /[A-Z]/.test(value);
        this.hasNumber = /[0-9]/.test(value);
        this.hasSpecialChar = /[@$!%*#?&]/.test(value);
    }

    // ControlValueAccessor Interface Implementation
    writeValue(value: any): void {
        if (value !== undefined) {
            this.control.setValue(value);
            this.updateValidation(value);
        }
    }

    registerOnChange(fn: any): void {
        this.onChange = fn;
    }

    registerOnTouched(fn: any): void {
        this.onTouch = fn;
    }
}
