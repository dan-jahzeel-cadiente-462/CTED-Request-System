document.addEventListener('DOMContentLoaded', () => {
    const commonRequestSelect = document.getElementById('common-request-select');
    const deviceSection = document.getElementById('device-request-section');
    const otherCheckbox = document.querySelector('[data-other-checkbox="true"]');
    const otherTextWrapper = document.getElementById('device-other-text-wrapper');
    const otherTextInput = document.getElementById('device-other-text');
    const studentID = document.getElementById('studentId');

    // Employment Certificate elements
    const employmentCertSection = document.getElementById('employment-cert-section');
    const employmentCertRadios = document.querySelectorAll('input[name="employment_cert"]');
    const employmentOtherRadio = document.querySelector('[data-employment-other-radio="true"]');
    const employmentOtherTextWrapper = document.getElementById('employment-cert-other-text-wrapper');
    const employmentOtherTextInput = document.getElementById('employment-cert-other-text');

    if (!commonRequestSelect) {
        return;
    }

    const devicesRequestValue = commonRequestSelect.dataset.devicesRequestValue || 'Request to Use Devices';
    const employmentCertRequestValue = commonRequestSelect.dataset.employmentCertRequestValue || 'Certificate of Employment';

    const resetEmploymentCert = () => {
        employmentCertRadios.forEach(radio => radio.checked = false);
        employmentOtherTextWrapper?.classList.add('hidden');
        if (employmentOtherTextInput) {
            employmentOtherTextInput.value = '';
        }
    };

    const updateEmploymentCertSection = () => {
        if (commonRequestSelect.value === employmentCertRequestValue) {
            employmentCertSection?.classList.remove('hidden');
        } else {
            employmentCertSection?.classList.add('hidden');
            resetEmploymentCert();
        }
    };

    const updateEmploymentOtherText = () => {
        if (employmentOtherRadio && employmentOtherRadio.checked) {
            employmentOtherTextWrapper?.classList.remove('hidden');
        } else {
            employmentOtherTextWrapper?.classList.add('hidden');
            if (employmentOtherTextInput) {
                employmentOtherTextInput.value = '';
            }
        }
    };

    commonRequestSelect.addEventListener('change', updateEmploymentCertSection);
    
    employmentCertRadios.forEach(radio => {
        radio.addEventListener('change', updateEmploymentOtherText);
    });

    updateEmploymentCertSection();
    updateEmploymentOtherText();

    const updateDeviceSection = () => {
        if (commonRequestSelect.value === devicesRequestValue) {
            deviceSection?.classList.remove('hidden');
        } else {
            deviceSection?.classList.add('hidden');
            otherTextWrapper?.classList.add('hidden');
            if (otherTextInput) {
                otherTextInput.value = '';
            }
            if (otherCheckbox) {
                otherCheckbox.checked = false;
            }
        }
    };

    const updateOtherText = () => {
        if (otherCheckbox && otherCheckbox.checked) {
            otherTextWrapper?.classList.remove('hidden');
        } else {
            otherTextWrapper?.classList.add('hidden');
            if (otherTextInput) {
                otherTextInput.value = '';
            }
        }
    };

    commonRequestSelect.addEventListener('change', updateDeviceSection);
    otherCheckbox?.addEventListener('change', updateOtherText);

    updateDeviceSection();
    updateOtherText();

    if (studentID) {
        studentID.addEventListener('keypress', (e) => {
            if (!/[0-9]/.test(e.key)) {
                e.preventDefault();
            }
        });
    }
});