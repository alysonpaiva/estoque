/**
 * JavaScript Personalizado
 * Sistema de Controle de Estoque - Pizzaria
 */

// Inicialização quando o DOM estiver carregado
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

/**
 * Inicializa a aplicação
 */
function initializeApp() {
    initializeTooltips();
    initializeAlerts();
    initializeFormValidation();
    initializeNumberFormatting();
    initializeConfirmDialogs();
    initializeSearchFilters();
    initializePrintButtons();
    initializeThemeToggle();
}

/**
 * Inicializa tooltips do Bootstrap
 */
function initializeTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

/**
 * Auto-hide alerts após 5 segundos
 */
function initializeAlerts() {
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert-dismissible');
        alerts.forEach(function(alert) {
            const bsAlert = new bootstrap.Alert(alert);
            if (bsAlert) {
                bsAlert.close();
            }
        });
    }, 5000);
}

/**
 * Validação de formulários
 */
function initializeFormValidation() {
    const forms = document.querySelectorAll('.needs-validation');
    
    Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
                
                // Focar no primeiro campo inválido
                const firstInvalid = form.querySelector(':invalid');
                if (firstInvalid) {
                    firstInvalid.focus();
                }
            }
            
            form.classList.add('was-validated');
        }, false);
    });
}

/**
 * Formatação automática de números
 */
function initializeNumberFormatting() {
    // Campos de moeda
    const moneyInputs = document.querySelectorAll('.money-input, input[name*="preco"], input[name*="valor"]');
    moneyInputs.forEach(function(input) {
        input.addEventListener('blur', function() {
            formatMoney(this);
        });
        
        input.addEventListener('keypress', function(e) {
            // Permitir apenas números, vírgula e ponto
            if (!/[\d,.]/.test(e.key) && !['Backspace', 'Delete', 'Tab', 'Enter'].includes(e.key)) {
                e.preventDefault();
            }
        });
    });
    
    // Campos de quantidade
    const quantityInputs = document.querySelectorAll('.quantity-input, input[name*="quantidade"]');
    quantityInputs.forEach(function(input) {
        input.addEventListener('blur', function() {
            formatQuantity(this);
        });
    });
}

/**
 * Formatar campo de moeda
 */
function formatMoney(input) {
    let value = input.value.replace(/[^\d,]/g, '');
    value = value.replace(/,/g, '.');
    
    if (value && !isNaN(value)) {
        input.value = parseFloat(value).toFixed(2).replace('.', ',');
    }
}

/**
 * Formatar campo de quantidade
 */
function formatQuantity(input) {
    let value = input.value.replace(/[^\d,]/g, '');
    value = value.replace(/,/g, '.');
    
    if (value && !isNaN(value)) {
        input.value = parseFloat(value).toFixed(3).replace('.', ',');
    }
}

/**
 * Diálogos de confirmação
 */
function initializeConfirmDialogs() {
    const confirmButtons = document.querySelectorAll('[data-confirm]');
    
    confirmButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm');
            if (!confirm(message)) {
                e.preventDefault();
                return false;
            }
        });
    });
}

/**
 * Filtros de busca em tempo real
 */
function initializeSearchFilters() {
    const searchInputs = document.querySelectorAll('.search-filter');
    
    searchInputs.forEach(function(input) {
        const targetTable = document.querySelector(input.getAttribute('data-target'));
        if (!targetTable) return;
        
        input.addEventListener('keyup', function() {
            const filter = this.value.toLowerCase();
            const rows = targetTable.querySelectorAll('tbody tr');
            
            rows.forEach(function(row) {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    });
}

/**
 * Botões de impressão
 */
function initializePrintButtons() {
    const printButtons = document.querySelectorAll('.btn-print');
    
    printButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            window.print();
        });
    });
}

/**
 * Toggle de tema (claro/escuro)
 */
function initializeThemeToggle() {
    const themeToggle = document.querySelector('#theme-toggle');
    if (!themeToggle) return;
    
    // Carregar tema salvo
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);
    
    themeToggle.addEventListener('click', function() {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
    });
}

/**
 * Utilitários
 */

/**
 * Mostrar loading em botão
 */
function showButtonLoading(button, text = 'Carregando...') {
    const originalText = button.innerHTML;
    button.innerHTML = `<span class="loading"></span> ${text}`;
    button.disabled = true;
    
    return function() {
        button.innerHTML = originalText;
        button.disabled = false;
    };
}

/**
 * Mostrar notificação toast
 */
function showToast(message, type = 'info') {
    const toastContainer = getOrCreateToastContainer();
    
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    toastContainer.appendChild(toast);
    
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    // Remover após esconder
    toast.addEventListener('hidden.bs.toast', function() {
        toast.remove();
    });
}

/**
 * Obter ou criar container de toasts
 */
function getOrCreateToastContainer() {
    let container = document.querySelector('.toast-container');
    
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        document.body.appendChild(container);
    }
    
    return container;
}

/**
 * Copiar texto para clipboard
 */
function copyToClipboard(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(function() {
            showToast('Texto copiado para a área de transferência!', 'success');
        });
    } else {
        // Fallback para navegadores mais antigos
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        showToast('Texto copiado para a área de transferência!', 'success');
    }
}

/**
 * Formatar número para exibição
 */
function formatNumber(number, decimals = 2) {
    return parseFloat(number).toLocaleString('pt-BR', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals
    });
}

/**
 * Formatar moeda para exibição
 */
function formatCurrency(value) {
    return parseFloat(value).toLocaleString('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    });
}

/**
 * Debounce function
 */
function debounce(func, wait, immediate) {
    let timeout;
    return function executedFunction() {
        const context = this;
        const args = arguments;
        
        const later = function() {
            timeout = null;
            if (!immediate) func.apply(context, args);
        };
        
        const callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        
        if (callNow) func.apply(context, args);
    };
}

/**
 * Validar CPF
 */
function validateCPF(cpf) {
    cpf = cpf.replace(/[^\d]/g, '');
    
    if (cpf.length !== 11 || /^(\d)\1{10}$/.test(cpf)) {
        return false;
    }
    
    let sum = 0;
    for (let i = 0; i < 9; i++) {
        sum += parseInt(cpf.charAt(i)) * (10 - i);
    }
    
    let remainder = (sum * 10) % 11;
    if (remainder === 10 || remainder === 11) remainder = 0;
    if (remainder !== parseInt(cpf.charAt(9))) return false;
    
    sum = 0;
    for (let i = 0; i < 10; i++) {
        sum += parseInt(cpf.charAt(i)) * (11 - i);
    }
    
    remainder = (sum * 10) % 11;
    if (remainder === 10 || remainder === 11) remainder = 0;
    
    return remainder === parseInt(cpf.charAt(10));
}

/**
 * Validar CNPJ
 */
function validateCNPJ(cnpj) {
    cnpj = cnpj.replace(/[^\d]/g, '');
    
    if (cnpj.length !== 14 || /^(\d)\1{13}$/.test(cnpj)) {
        return false;
    }
    
    let length = cnpj.length - 2;
    let numbers = cnpj.substring(0, length);
    let digits = cnpj.substring(length);
    let sum = 0;
    let pos = length - 7;
    
    for (let i = length; i >= 1; i--) {
        sum += numbers.charAt(length - i) * pos--;
        if (pos < 2) pos = 9;
    }
    
    let result = sum % 11 < 2 ? 0 : 11 - sum % 11;
    if (result !== parseInt(digits.charAt(0))) return false;
    
    length = length + 1;
    numbers = cnpj.substring(0, length);
    sum = 0;
    pos = length - 7;
    
    for (let i = length; i >= 1; i--) {
        sum += numbers.charAt(length - i) * pos--;
        if (pos < 2) pos = 9;
    }
    
    result = sum % 11 < 2 ? 0 : 11 - sum % 11;
    
    return result === parseInt(digits.charAt(1));
}

/**
 * Máscara para telefone
 */
function phoneMask(value) {
    return value
        .replace(/\D/g, '')
        .replace(/(\d{2})(\d)/, '($1) $2')
        .replace(/(\d{4})(\d)/, '$1-$2')
        .replace(/(\d{4})-(\d)(\d{4})/, '$1$2-$3')
        .replace(/(-\d{4})\d+?$/, '$1');
}

/**
 * Máscara para CEP
 */
function cepMask(value) {
    return value
        .replace(/\D/g, '')
        .replace(/(\d{5})(\d)/, '$1-$2')
        .replace(/(-\d{3})\d+?$/, '$1');
}

/**
 * Exportar dados para CSV
 */
function exportToCSV(data, filename = 'dados.csv') {
    const csv = data.map(row => 
        row.map(field => `"${field}"`).join(',')
    ).join('\n');
    
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    
    if (link.download !== undefined) {
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', filename);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
}

/**
 * Função global para confirmar exclusão
 */
function confirmarExclusao(id, nome, formId = null) {
    const message = `Tem certeza que deseja excluir "${nome}"?\n\nEsta ação não pode ser desfeita!`;
    
    if (confirm(message)) {
        if (formId) {
            const form = document.getElementById(formId);
            if (form) {
                const idInput = form.querySelector('input[name="id"]');
                if (idInput) {
                    idInput.value = id;
                }
                form.submit();
            }
        }
        return true;
    }
    
    return false;
}

// Expor funções globalmente
window.EstoqueApp = {
    showToast,
    copyToClipboard,
    formatNumber,
    formatCurrency,
    validateCPF,
    validateCNPJ,
    phoneMask,
    cepMask,
    exportToCSV,
    confirmarExclusao,
    showButtonLoading
};

