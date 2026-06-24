<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <style>
        @font-face {
            font-family: 'Cairo';
            font-style: normal;
            font-weight: 400;
            src: url('{{ storage_path('fonts/Cairo-Regular.ttf') }}') format('truetype');
        }

        @page { margin: 18px; }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: 'Cairo', 'DejaVu Sans', sans-serif;
            color: #0f172a;
            direction: rtl;
            text-align: right;
            background: #eff6ff;
        }

        .page {
            min-height: 780px;
            border: 10px solid #1e3a8a;
            padding: 14px;
            position: relative;
            background: linear-gradient(180deg, #eff6ff 0%, #ffffff 36%, #fff7ed 100%);
        }

        .inner {
            min-height: 752px;
            position: relative;
            overflow: hidden;
            border: 1px solid #bfdbfe;
            background: rgba(255, 255, 255, 0.93);
            padding: 28px 32px 24px;
        }

        .inner::before,
        .inner::after {
            content: '';
            position: absolute;
            width: 120px;
            height: 120px;
            border: 10px solid rgba(30, 58, 138, 0.08);
            border-radius: 28px;
            pointer-events: none;
        }

        .inner::before {
            top: -36px;
            left: -36px;
        }

        .inner::after {
            bottom: -36px;
            right: -36px;
        }

        .watermark {
            position: absolute;
            inset: 250px 0 auto 0;
            text-align: center;
            font-size: 84px;
            font-weight: 700;
            color: #eff6ff;
            opacity: 0.9;
            z-index: 0;
            letter-spacing: 4px;
        }

        .content {
            position: relative;
            z-index: 1;
        }

        .center { text-align: center; }
        .muted { color: #64748b; }
        .ltr { direction: ltr; unicode-bidi: embed; display: inline-block; }

        .ministry {
            display: inline-block;
            padding: 5px 14px;
            border-radius: 999px;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e3a8a;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .institute {
            margin: 0;
            font-size: 24px;
            color: #0f172a;
            line-height: 1.5;
        }

        .subtitle {
            margin-top: 8px;
            font-size: 13px;
            color: #64748b;
        }

        .header-card {
            margin: 18px auto 0;
            max-width: 560px;
            border: 1px solid #dbeafe;
            border-radius: 18px;
            background: #ffffff;
            padding: 14px 18px;
            box-shadow: 0 12px 32px rgba(15, 23, 42, 0.06);
        }

        .certificate-number {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 14px;
            border-radius: 999px;
            background: #fff7ed;
            color: #9a3412;
            font-size: 13px;
            font-weight: 700;
            border: 1px solid #fed7aa;
        }

        .title {
            margin: 28px auto 22px;
            display: inline-block;
            min-width: 300px;
            padding: 14px 30px;
            border-radius: 22px;
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            color: #ffffff;
            text-align: center;
            font-size: 32px;
            font-weight: 700;
            box-shadow: 0 16px 32px rgba(30, 58, 138, 0.18);
        }

        .statement {
            margin: 0 auto;
            width: 92%;
            text-align: center;
            font-size: 18px;
            line-height: 2.35;
            color: #1f2937;
        }

        .student-name {
            color: #1e3a8a;
            font-size: 22px;
            font-weight: 700;
            border-bottom: 1px solid #94a3b8;
            padding: 0 12px 3px;
        }

        .meta-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-top: 26px;
        }

        .meta-card {
            min-height: 72px;
            border: 1px solid #dbeafe;
            border-radius: 14px;
            background: #f8fafc;
            padding: 10px 12px;
        }

        .meta-card strong {
            display: block;
            color: #1e3a8a;
            margin-bottom: 6px;
            font-size: 12px;
        }

        .signature-area {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
            margin-top: 72px;
        }

        .signature-line {
            padding-top: 14px;
            border-top: 1px solid #94a3b8;
            text-align: center;
            color: #1e3a8a;
            font-size: 14px;
            font-weight: 700;
        }

        .printed {
            margin-top: 28px;
            text-align: center;
            font-size: 11px;
            color: #64748b;
        }
    </style>
</head>
<body>
@php
    $t = fn (mixed $value) => $pdfText->text($value);
@endphp
<div class="page">
    <div class="inner">
        <div class="watermark">{{ $t('تخرج') }}</div>

        <div class="content">
            <div class="center">
                <div class="ministry">{{ $t('دولة ليبيا') }}</div>
                <h1 class="institute">{{ $t('المعهد العالي للعلوم والتقنية - العجيلات') }}</h1>
                <div class="subtitle">{{ $t('شهادة رسمية تصدر بعد اعتماد استيفاء متطلبات التخرج') }}</div>
                <div class="header-card">
                    <div class="certificate-number">
                        {{ $t('رقم الشهادة') }}:
                        <span class="ltr">{{ $record->certificate_number }}</span>
                    </div>
                </div>
                <div class="title">{{ $t('شهادة تخرج') }}</div>
            </div>

            <p class="statement">
                {{ $t('يشهد المعهد العالي للعلوم والتقنية - العجيلات بأن الطالب/ة') }}
                <span class="student-name">{{ $t($record->student->full_name) }}</span>
                {{ $t('رقم القيد') }}
                <strong class="ltr">{{ $record->student->registration_number }}</strong>
                {{ $t('قد أتم/ت بنجاح جميع متطلبات التخرج المعتمدة للتخصص، واستحق/ت هذه الشهادة.') }}
            </p>

            <div class="meta-grid">
                <div class="meta-card">
                    <strong>{{ $t('القسم') }}</strong>
                    {{ $t($record->specialization->department?->name ?? '-') }}
                </div>
                <div class="meta-card">
                    <strong>{{ $t('التخصص') }}</strong>
                    {{ $t($record->specialization->name) }}
                </div>
                <div class="meta-card">
                    <strong>{{ $t('تاريخ التخرج') }}</strong>
                    <span class="ltr">{{ $record->graduation_date?->format('Y-m-d') }}</span>
                </div>
                <div class="meta-card">
                    <strong>{{ $t('المعدل التراكمي') }}</strong>
                    <span class="ltr">{{ number_format((float) $record->cgpa, 2) }}%</span>
                </div>
                <div class="meta-card">
                    <strong>{{ $t('التقدير') }}</strong>
                    {{ $t($evaluation) }}
                </div>
                <div class="meta-card">
                    <strong>{{ $t('إجمالي الوحدات') }}</strong>
                    <span class="ltr">{{ $record->total_units }}</span>
                </div>
            </div>

            <div class="signature-area">
                <div class="signature-line">{{ $t('مسجل الكلية') }}</div>
                <div class="signature-line">{{ $t('رئيس القسم') }}</div>
                <div class="signature-line">{{ $t('عميد المعهد') }}</div>
            </div>

            <p class="printed">
                {{ $t('تاريخ الطباعة') }}:
                <span class="ltr">{{ $printed_at->format('Y-m-d H:i') }}</span>
            </p>
        </div>
    </div>
</div>
</body>
</html>
