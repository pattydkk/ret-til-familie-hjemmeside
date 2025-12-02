<?php
/**
 * Template Name: Platform - Rapporter
 */

if (!is_user_logged_in()) {
    wp_redirect(home_url('/platform-auth'));
    exit;
}

get_header('platform');

$current_user = wp_get_current_user();
$user_id = $current_user->ID;
$language = get_user_meta($user_id, 'language_preference', true) ?: 'da_DK';
$is_danish = ($language === 'da_DK');
$is_swedish = ($language === 'sv_SE');
$is_english = ($language === 'en_US');

$strings = [
    'da_DK' => [
        'title' => 'Rapporter & Analyser',
        'subtitle' => 'Juridiske, psykologiske og socialfaglige rapporter fra Ret til Familie',
        'filters' => 'Filtre',
        'country' => 'Land',
        'city' => 'By',
        'case_type' => 'Sagstype',
        'report_type' => 'Rapporttype',
        'all' => 'Alle',
        'denmark' => 'Danmark',
        'sweden' => 'Sverige',
        'legal' => 'Juridisk',
        'psychological' => 'Psykologisk',
        'social' => 'Socialfaglig',
        'apply_filters' => 'Anvend filtre',
        'reset' => 'Nulstil',
        'download' => 'Download',
        'downloads' => 'Downloads',
        'published' => 'Udgivet',
        'no_reports' => 'Ingen rapporter fundet',
        'loading' => 'Indlæser...'
    ],
    'sv_SE' => [
        'title' => 'Rapporter & Analyser',
        'subtitle' => 'Juridiska, psykologiska och sociala rapporter från Rätt till Familj',
        'filters' => 'Filter',
        'country' => 'Land',
        'city' => 'Stad',
        'case_type' => 'Ärendetyp',
        'report_type' => 'Rapporttyp',
        'all' => 'Alla',
        'denmark' => 'Danmark',
        'sweden' => 'Sverige',
        'legal' => 'Juridisk',
        'psychological' => 'Psykologisk',
        'social' => 'Social',
        'apply_filters' => 'Tillämpa filter',
        'reset' => 'Återställ',
        'download' => 'Ladda ner',
        'downloads' => 'Nedladdningar',
        'published' => 'Publicerad',
        'no_reports' => 'Inga rapporter hittades',
        'loading' => 'Laddar...'
    ],
    'en_US' => [
        'title' => 'Reports & Analysis',
        'subtitle' => 'Legal, psychological and social reports from Right to Family',
        'filters' => 'Filters',
        'country' => 'Country',
        'city' => 'City',
        'case_type' => 'Case Type',
        'report_type' => 'Report Type',
        'all' => 'All',
        'denmark' => 'Denmark',
        'sweden' => 'Sweden',
        'legal' => 'Legal',
        'psychological' => 'Psychological',
        'social' => 'Social',
        'apply_filters' => 'Apply filters',
        'reset' => 'Reset',
        'download' => 'Download',
        'downloads' => 'Downloads',
        'published' => 'Published',
        'no_reports' => 'No reports found',
        'loading' => 'Loading...'
    ]
];

$t = $strings[$language];
?>

<div class="platform-container">
    <?php get_template_part('template-parts/platform-sidebar'); ?>
    
    <div class="platform-content">
        <div class="rtf-card">
            <div class="page-header">
                <h1><?php echo esc_html($t['title']); ?></h1>
                <p class="subtitle"><?php echo esc_html($t['subtitle']); ?></p>
            </div>
            
            <!-- Filters Section -->
            <div class="filters-section">
                <h3><?php echo esc_html($t['filters']); ?></h3>
                <div class="filter-grid">
                    <div class="filter-item">
                        <label><?php echo esc_html($t['country']); ?></label>
                        <select id="filterCountry">
                            <option value=""><?php echo esc_html($t['all']); ?></option>
                            <option value="DK">🇩🇰 <?php echo esc_html($t['denmark']); ?></option>
                            <option value="SE">🇸🇪 <?php echo esc_html($t['sweden']); ?></option>
                        </select>
                    </div>
                    
                    <div class="filter-item">
                        <label><?php echo esc_html($t['city']); ?></label>
                        <select id="filterCity">
                            <option value=""><?php echo esc_html($t['all']); ?></option>
                        </select>
                    </div>
                    
                    <div class="filter-item">
                        <label><?php echo esc_html($t['case_type']); ?></label>
                        <select id="filterCaseType">
                            <option value=""><?php echo esc_html($t['all']); ?></option>
                        </select>
                    </div>
                    
                    <div class="filter-item">
                        <label><?php echo esc_html($t['report_type']); ?></label>
                        <select id="filterReportType">
                            <option value=""><?php echo esc_html($t['all']); ?></option>
                            <option value="juridisk"><?php echo esc_html($t['legal']); ?></option>
                            <option value="psykologisk"><?php echo esc_html($t['psychological']); ?></option>
                            <option value="socialfaglig"><?php echo esc_html($t['social']); ?></option>
                        </select>
                    </div>
                </div>
                
                <div class="filter-actions">
                    <button id="applyFilters" class="btn-primary">
                        <i class="fas fa-filter"></i> <?php echo esc_html($t['apply_filters']); ?>
                    </button>
                    <button id="resetFilters" class="btn-secondary">
                        <i class="fas fa-redo"></i> <?php echo esc_html($t['reset']); ?>
                    </button>
                </div>
            </div>
            
            <!-- Reports Grid -->
            <div id="reportsContainer" class="reports-grid">
                <div class="loading-spinner">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p><?php echo esc_html($t['loading']); ?></p>
                </div>
            </div>
            
            <!-- Pagination -->
            <div id="pagination" class="pagination" style="display: none;"></div>
        </div>
    </div>
</div>

<style>
.page-header {
    margin-bottom: 30px;
}

.page-header h1 {
    font-size: 32px;
    margin-bottom: 10px;
    color: var(--rtf-text);
}

.subtitle {
    font-size: 16px;
    color: var(--rtf-text-secondary);
}

.filters-section {
    background: var(--rtf-bg);
    padding: 25px;
    border-radius: 12px;
    margin-bottom: 30px;
    border: 1px solid var(--rtf-border);
}

.filters-section h3 {
    margin-bottom: 20px;
    color: var(--rtf-text);
}

.filter-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.filter-item label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: var(--rtf-text);
}

.filter-item select {
    width: 100%;
    padding: 10px 15px;
    border: 1px solid var(--rtf-border);
    border-radius: 8px;
    font-size: 14px;
    background: white;
}

.filter-actions {
    display: flex;
    gap: 15px;
}

.btn-primary {
    padding: 12px 24px;
    background: var(--rtf-primary);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
}

.btn-primary:hover {
    background: var(--rtf-primary-dark);
    transform: translateY(-2px);
}

.btn-secondary {
    padding: 12px 24px;
    background: white;
    color: var(--rtf-text);
    border: 1px solid var(--rtf-border);
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
}

.btn-secondary:hover {
    background: var(--rtf-hover);
}

.reports-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 25px;
    margin-bottom: 30px;
}

.report-card {
    background: white;
    border: 1px solid var(--rtf-border);
    border-radius: 12px;
    padding: 20px;
    transition: all 0.2s;
}

.report-card:hover {
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    transform: translateY(-4px);
}

.report-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 15px;
}

.report-icon {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}

.report-icon.juridisk {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
}

.report-icon.psykologisk {
    background: linear-gradient(135deg, #8b5cf6, #6d28d9);
}

.report-icon.socialfaglig {
    background: linear-gradient(135deg, #10b981, #059669);
}

.report-badges {
    display: flex;
    gap: 5px;
}

.report-badge {
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    background: var(--rtf-primary-light);
    color: var(--rtf-primary);
}

.report-title {
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 10px;
    color: var(--rtf-text);
}

.report-description {
    font-size: 14px;
    color: var(--rtf-text-secondary);
    margin-bottom: 15px;
    line-height: 1.6;
}

.report-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    font-size: 12px;
    color: var(--rtf-text-secondary);
}

.report-actions {
    display: flex;
    gap: 10px;
}

.btn-download {
    flex: 1;
    padding: 10px;
    background: var(--rtf-primary);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.2s;
}

.btn-download:hover {
    background: var(--rtf-primary-dark);
}

.loading-spinner {
    text-align: center;
    padding: 60px;
    color: var(--rtf-text-secondary);
}

.loading-spinner i {
    font-size: 48px;
    margin-bottom: 15px;
}

.pagination {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-top: 30px;
}

.pagination button {
    padding: 8px 16px;
    border: 1px solid var(--rtf-border);
    background: white;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
}

.pagination button:hover:not(:disabled) {
    background: var(--rtf-primary);
    color: white;
    border-color: var(--rtf-primary);
}

.pagination button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.pagination button.active {
    background: var(--rtf-primary);
    color: white;
    border-color: var(--rtf-primary);
}

@media (max-width: 768px) {
    .filter-grid {
        grid-template-columns: 1fr;
    }
    
    .reports-grid {
        grid-template-columns: 1fr;
    }
    
    .filter-actions {
        flex-direction: column;
    }
    
    .filter-actions button {
        width: 100%;
    }
}
</style>

<script>
const ReportsApp = {
    currentPage: 1,
    totalPages: 1,
    filters: {},
    language: '<?php echo $language; ?>',
    
    strings: <?php echo json_encode($strings); ?>,
    
    init() {
        this.loadFilterOptions();
        this.loadReports();
        this.attachEventListeners();
    },
    
    attachEventListeners() {
        document.getElementById('applyFilters').addEventListener('click', () => {
            this.applyFilters();
        });
        
        document.getElementById('resetFilters').addEventListener('click', () => {
            this.resetFilters();
        });
        
        // Auto-load cities when country changes
        document.getElementById('filterCountry').addEventListener('change', (e) => {
            this.loadCitiesByCountry(e.target.value);
        });
    },
    
    async loadFilterOptions() {
        try {
            const response = await fetch('/wp-json/kate/v1/reports/filters', {
                credentials: 'same-origin'
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.populateFilterOptions(data);
            }
        } catch (error) {
            console.error('Error loading filter options:', error);
        }
    },
    
    populateFilterOptions(data) {
        // Populate case types
        const caseTypeSelect = document.getElementById('filterCaseType');
        data.case_types.forEach(type => {
            const option = document.createElement('option');
            option.value = type;
            option.textContent = type;
            caseTypeSelect.appendChild(option);
        });
    },
    
    async loadCitiesByCountry(country) {
        const citySelect = document.getElementById('filterCity');
        citySelect.innerHTML = '<option value="">' + this.strings[this.language].all + '</option>';
        
        if (!country) return;
        
        try {
            const response = await fetch(`/wp-json/kate/v1/reports/filters?country=${country}`, {
                credentials: 'same-origin'
            });
            
            const data = await response.json();
            
            if (data.success && data.cities) {
                data.cities.forEach(city => {
                    const option = document.createElement('option');
                    option.value = city;
                    option.textContent = city;
                    citySelect.appendChild(option);
                });
            }
        } catch (error) {
            console.error('Error loading cities:', error);
        }
    },
    
    applyFilters() {
        this.filters = {
            country: document.getElementById('filterCountry').value,
            city: document.getElementById('filterCity').value,
            case_type: document.getElementById('filterCaseType').value,
            report_type: document.getElementById('filterReportType').value
        };
        
        this.currentPage = 1;
        this.loadReports();
    },
    
    resetFilters() {
        document.getElementById('filterCountry').value = '';
        document.getElementById('filterCity').value = '';
        document.getElementById('filterCaseType').value = '';
        document.getElementById('filterReportType').value = '';
        
        this.filters = {};
        this.currentPage = 1;
        this.loadReports();
    },
    
    async loadReports() {
        const container = document.getElementById('reportsContainer');
        container.innerHTML = '<div class="loading-spinner"><i class="fas fa-spinner fa-spin"></i></div>';
        
        try {
            const params = new URLSearchParams({
                ...this.filters,
                limit: 12,
                offset: (this.currentPage - 1) * 12
            });
            
            const response = await fetch('/wp-json/kate/v1/reports?' + params, {
                credentials: 'same-origin'
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.renderReports(data.reports);
                this.renderPagination(data.total);
            } else {
                container.innerHTML = '<p style="text-align: center; padding: 40px;">' + 
                    this.strings[this.language].no_reports + '</p>';
            }
        } catch (error) {
            console.error('Error loading reports:', error);
            container.innerHTML = '<p style="text-align: center; color: var(--rtf-error);">Error loading reports</p>';
        }
    },
    
    renderReports(reports) {
        const container = document.getElementById('reportsContainer');
        const t = this.strings[this.language];
        
        if (reports.length === 0) {
            container.innerHTML = '<p style="text-align: center; padding: 40px;">' + t.no_reports + '</p>';
            return;
        }
        
        container.innerHTML = reports.map(report => `
            <div class="report-card">
                <div class="report-header">
                    <div class="report-icon ${report.report_type}">
                        ${this.getReportIcon(report.report_type)}
                    </div>
                    <div class="report-badges">
                        <span class="report-badge">${report.country === 'DK' ? '🇩🇰' : '🇸🇪'}</span>
                    </div>
                </div>
                
                <h3 class="report-title">${this.escapeHtml(report.title)}</h3>
                <p class="report-description">${this.escapeHtml(report.description)}</p>
                
                <div class="report-meta">
                    <span><i class="fas fa-download"></i> ${report.download_count} ${t.downloads}</span>
                    <span><i class="fas fa-calendar"></i> ${this.formatDate(report.published_date)}</span>
                </div>
                
                <div class="report-actions">
                    <button class="btn-download" onclick="ReportsApp.downloadReport(${report.id}, '${report.file_url}')">
                        <i class="fas fa-download"></i> ${t.download}
                    </button>
                </div>
            </div>
        `).join('');
    },
    
    getReportIcon(type) {
        const icons = {
            'juridisk': '⚖️',
            'psykologisk': '🧠',
            'socialfaglig': '👥'
        };
        return icons[type] || '📄';
    },
    
    renderPagination(total) {
        this.totalPages = Math.ceil(total / 12);
        
        if (this.totalPages <= 1) {
            document.getElementById('pagination').style.display = 'none';
            return;
        }
        
        const pagination = document.getElementById('pagination');
        pagination.style.display = 'flex';
        
        let html = '';
        
        // Previous button
        html += `<button ${this.currentPage === 1 ? 'disabled' : ''} onclick="ReportsApp.changePage(${this.currentPage - 1})">
            <i class="fas fa-chevron-left"></i>
        </button>`;
        
        // Page numbers
        for (let i = 1; i <= this.totalPages; i++) {
            if (i === 1 || i === this.totalPages || (i >= this.currentPage - 2 && i <= this.currentPage + 2)) {
                html += `<button class="${i === this.currentPage ? 'active' : ''}" onclick="ReportsApp.changePage(${i})">${i}</button>`;
            } else if (i === this.currentPage - 3 || i === this.currentPage + 3) {
                html += '<span>...</span>';
            }
        }
        
        // Next button
        html += `<button ${this.currentPage === this.totalPages ? 'disabled' : ''} onclick="ReportsApp.changePage(${this.currentPage + 1})">
            <i class="fas fa-chevron-right"></i>
        </button>`;
        
        pagination.innerHTML = html;
    },
    
    changePage(page) {
        if (page < 1 || page > this.totalPages) return;
        this.currentPage = page;
        this.loadReports();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    },
    
    async downloadReport(reportId, fileUrl) {
        try {
            // Track download
            await fetch(`/wp-json/kate/v1/reports/${reportId}`, {
                credentials: 'same-origin'
            });
            
            // Trigger download
            window.open(fileUrl, '_blank');
            
        } catch (error) {
            console.error('Error downloading report:', error);
        }
    },
    
    formatDate(date) {
        return new Date(date).toLocaleDateString(this.language.replace('_', '-'));
    },
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
};

document.addEventListener('DOMContentLoaded', () => {
    ReportsApp.init();
});
</script>

<?php get_footer('platform'); ?>
