@page { size: A4 landscape; margin: 0; }

.certificate-page,
.certificate-page * { box-sizing: border-box; }

.certificate-page {
    position: relative;
    width: 297mm;
    height: 210mm;
    overflow: hidden;
    color: #111;
    font-family: DejaVu Sans, sans-serif;
    text-align: center;
}

.certificate-background {
    position: absolute;
    inset: 0;
    width: 297mm;
    height: 210mm;
}

.certificate-top-logos {
    position: absolute;
    top: 9mm;
    right: 18mm;
    display: flex;
    align-items: center;
    gap: 2.5mm;
    height: 13mm;
}

.certificate-top-logo-quality {
    width: 22mm;
    height: auto;
}

.certificate-top-logo-ramah {
    width: 19mm;
    height: auto;
}

.certificate-content {
    position: absolute;
    top: 9mm;
    right: 30mm;
    bottom: 10mm;
    left: 30mm;
}

.certificate-logo {
    width: 19mm;
    height: 19mm;
    margin: 0 auto 1mm;
}

.certificate-institution {
    font-size: 14pt;
    font-weight: 700;
    line-height: 1.12;
}

.certificate-title {
    margin-top: 7mm;
    font-size: 25pt;
    font-weight: 700;
    letter-spacing: .4mm;
}

.certificate-number {
    margin-top: 1mm;
    font-size: 11pt;
}

.certificate-recipient {
    margin: 7mm 0 2mm;
    font-size: 12pt;
}

.certificate-name {
    display: inline-block;
    border-bottom: 1px solid #111;
    font-size: 17pt;
    font-weight: 700;
}

.certificate-institution-name {
    margin-top: 3mm;
    font-size: 12pt;
}

.certificate-description {
    max-width: 220mm;
    margin: 5mm auto 0;
    font-size: 11.5pt;
    line-height: 1.35;
}

.certificate-activity {
    font-size: 12.5pt;
    font-weight: 700;
}

.certificate-signature {
    position: absolute;
    right: 0;
    bottom: 18mm;
    left: 0;
    font-size: 11pt;
    line-height: 1.15;
}

.certificate-qr-wrap {
    height: 19mm;
    margin: .5mm auto 0;
}

.certificate-qr {
    display: block;
    width: 18mm;
    height: 18mm;
    margin: 0 auto;
}

.certificate-signature-space {
    height: 17mm;
}

.certificate-signature-name {
    font-size: 12pt;
    font-weight: 700;
}

.certificate-materials-page {
    position: relative;
    width: 297mm;
    height: 210mm;
    overflow: hidden;
    color: #111;
    font-family: DejaVu Sans, sans-serif;
    text-align: center;
    page-break-before: always;
}

.certificate-materials-background {
    position: absolute;
    inset: 0;
    width: 297mm;
    height: 210mm;
}

.certificate-materials-title {
    position: relative;
    z-index: 1;
    padding: 17mm 20mm 0;
    font-size: 16pt;
    font-weight: 700;
}

.certificate-materials-table {
    position: relative;
    z-index: 1;
    width: calc(100% - 52mm);
    margin: 18mm 26mm 0;
    border-collapse: collapse;
    table-layout: fixed;
    font-size: 12pt;
    text-align: left;
}

.certificate-materials-table th,
.certificate-materials-table td {
    border: .25mm solid #1f2933;
    padding: 3.1mm 2.5mm;
    vertical-align: middle;
}

.certificate-materials-table thead th {
    background: #0f3b67;
    color: #fff;
    font-weight: 700;
    text-align: center;
}

.certificate-materials-table th:first-child,
.certificate-materials-table td:first-child {
    width: 18mm;
    text-align: center;
}

.certificate-materials-table th:last-child,
.certificate-materials-table td:last-child {
    width: 23mm;
    text-align: center;
}

.certificate-materials-table tfoot td {
    background: #b9cbed;
    font-weight: 700;
    text-align: center;
}

.certificate-materials-empty {
    color: #6b7280;
    text-align: center;
}
