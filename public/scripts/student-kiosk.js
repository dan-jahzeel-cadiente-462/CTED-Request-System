document.addEventListener('DOMContentLoaded', () => {
    const commonRequestSelect = document.getElementById('common-request-select');
    const programSelect = document.getElementById('program-select');
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

    // Other common requests
    const otherCommonRequestSection = document.getElementById('other-requirement-text-section');
    const otherCommonRequestTextWrapper = document.getElementById('other-requirement-text-wrapper');
    const otherCommonRequestInput = document.getElementById('other-requirement-text');

    // Other programs/courses to be selected
    const otherProgramSection = document.getElementById('other-program-text-section');
    const otherProgramTextWrapper = document.getElementById('other-program-text-wrapper');
    const otherProgramInput = document.getElementById('other-program-text');

    // program select / other program handling (guarded)
    const otherProgramValue = programSelect?.dataset?.otherProgramValue || 'Other';

    const updateOtherProgramSection = () => {
        if (!programSelect) return;
        if (programSelect.value === otherProgramValue) {
            otherProgramSection?.classList.remove('hidden');
            otherProgramTextWrapper?.classList.remove('hidden');
            if (otherProgramInput) otherProgramInput.value = "";
        } else {
            otherProgramSection?.classList.add('hidden');
            otherProgramTextWrapper?.classList.add('hidden');
        }
    }

    const initSelectChevronBehavior = () => {
        document.querySelectorAll('.select-with-chevron').forEach((wrapper) => {
            const select = wrapper.querySelector('select');
            const chevron = wrapper.querySelector('.chevron-icon-wrapper');

            if (!select || !chevron) {
                return;
            }

            const setChevronExpanded = (expanded) => {
                chevron.classList.toggle('rotate-180', expanded);
                chevron.classList.toggle('text-blue-600', expanded);
                chevron.classList.toggle('text-slate-400', !expanded);
            };

            select.addEventListener('focus', () => setChevronExpanded(true));
            select.addEventListener('blur', () => setChevronExpanded(false));
        });
    };

    if (programSelect) {
        programSelect.addEventListener('change', updateOtherProgramSection);
        updateOtherProgramSection();
    }
    initSelectChevronBehavior();


    if (!commonRequestSelect) {
        return;
    }

    const devicesRequestValue = commonRequestSelect.dataset?.devicesRequestValue || 'Request to Use Devices';
    const employmentCertRequestValue = commonRequestSelect.dataset?.employmentCertRequestValue || 'Certificate of Employment';
    const otherCommonRequestValue = commonRequestSelect.dataset?.otherCommonRequestValue || 'Other';

    const updateOtherRequirementSection = () => {
        if (commonRequestSelect.value === otherCommonRequestValue) {
            otherCommonRequestSection?.classList.remove('hidden');
            otherCommonRequestTextWrapper?.classList.remove('hidden');
        } else {
            otherCommonRequestSection?.classList.add('hidden');
            otherCommonRequestTextWrapper?.classList.add('hidden');
            otherCommonRequestInput.value = "";
        }
    }

    commonRequestSelect.addEventListener('change', updateOtherRequirementSection);
    updateOtherRequirementSection();


    /**
     * If the user selects "Certificate of Employment" in the dropdown, then the options would be either 'Local' or
     * 'International as in the radio.
     * FUNCTIONS
     * resetEmploymentCert() = 
     * updateEmploymentCertSection() = 
     * updateEmploymentOtherText() = 
     */
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

    // Handle form submission: collect inputs, checkboxes, radios and post JSON
    const form = document.getElementById('student-kiosk-form');
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const fullName = document.getElementById('fullName')?.value || '';
            const contactNo = document.getElementById('contactNo')?.value || '';
            const studentIdVal = document.getElementById('studentId')?.value || '';
            const programVal = document.getElementById('program-select')?.value || '';
            const programOther = document.getElementById('other-program-text')?.value || '';
            const commonRequestVal = document.getElementById('common-request-select')?.value || '';

            // devices
            const deviceCheckboxes = Array.from(document.querySelectorAll('input[name="devices[]"]:checked'));
            const devices = deviceCheckboxes.map(cb => cb.value);
            const deviceOther = document.getElementById('device-other-text')?.value || '';

            // employment cert
            const employmentRadio = document.querySelector('input[name="employment_cert"]:checked');
            const employmentVal = employmentRadio ? employmentRadio.value : '';
            const requestOther = document.getElementById('other-requirement-text')?.value || '';

            const payload = {
                studentId: studentIdVal,
                fullName: fullName,
                contactNo: contactNo,
                program: programVal,
                program_other: programOther,
                commonRequest: commonRequestVal,
                devices: devices,
                device_other: deviceOther,
                employment_cert: employmentVal,
                request_other: requestOther
            };

            try {
                const res = await fetch(form.action || '/cted/student-kiosk/submit', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                if (res.ok) {
                    form.reset();
                    alert('Request submitted. Thank you!');
                    window.location.reload();
                } else {
                    const body = await res.json().catch(() => ({}));
                    // fallback to native submit if server rejects JSON (lets server handle form post)
                    console.warn('Fetch failed, falling back to native submit', body);
                    form.submit();
                }
            } catch (err) {
                console.error(err);
                // network or other JS failure: fall back to browser form submit
                form.submit();
            }
        });
    }
});