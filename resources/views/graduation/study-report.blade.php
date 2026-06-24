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

        @page { margin: 22px; }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: 'Cairo', 'DejaVu Sans', sans-serif;
            color: #111827;
            font-size: 11px;
            direction: rtl;
            text-align: right;
            background: #f8fafc;
        }

        h1, h2, h3 { margin: 0; color: #1e3a8a; }
        .ltr { direction: ltr; unicode-bidi: embed; display: inline-block; }

        .report {
            min-height: 760px;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            border-radius: 18px;
            overflow: hidden;
        }

        .hero {
            position: relative;
            padding: 24px 28px 18px;
            background: linear-gradient(135deg, #eff6ff 0%, #ffffff 55%, #fff7ed 100%);
            border-bottom: 1px solid #dbeafe;
        }

        .hero::before,
        .hero::after {
            content: '';
            position: absolute;
            border-radius: 999px;
            pointer-events: none;
            opacity: 0.5;
        }

        .hero::before {
            width: 130px;
            height: 130px;
            background: rgba(37, 99, 235, 0.08);
            top: -32px;
            left: -28px;
        }

        .hero::after {
            width: 180px;
            height: 180px;
            background: rgba(249, 115, 22, 0.08);
            bottom: -72px;
            right: -46px;
        }

        .hero-top {
            position: relative;
            z-index: 1;
            display: flex;
            justify-content: space-between;
            gap: 18px;
            align-items: flex-start;
        }

        .hero-title {
            font-size: 22px;
            line-height: 1.55;
        }

        .hero-subtitle {
            margin-top: 8px;
            color: #64748b;
            line-height: 1.9;
            max-width: 720px;
        }

        .header-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 13px;
            border-radius: 999px;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e3a8a;
            font-weight: 700;
            font-size: 12px;
            white-space: nowrap;
        }

        .student-card {
            margin-top: 16px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }

        .card {
            background: #ffffff;
            border: 1px solid #dbeafe;
            border-radius: 14px;
            padding: 10px 12px;
            min-height: 68px;
        }

        .card strong {
            display: block;
            color: #1e3a8a;
            font-size: 12px;
            margin-bottom: 6px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-top: 14px;
        }

        .summary-item {
            border: 1px solid #e2e8f0;
            background: #ffffff;
            border-radius: 14px;
            padding: 11px 12px;
            min-height: 72px;
        }

        .summary-item .label {
            color: #64748b;
            font-size: 10px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .summary-item .value {
            color: #0f172a;
            font-size: 20px;
            line-height: 1.2;
            font-weight: 700;
        }

        .summary-item.accent-blue { border-color: #bfdbfe; background: #eff6ff; }
        .summary-item.accent-orange { border-color: #fed7aa; background: #fff7ed; }
        .summary-item.accent-green { border-color: #bbf7d0; background: #f0fdf4; }
        .summary-item.accent-slate { border-color: #e2e8f0; background: #f8fafc; }

        .meta-strip {
            margin: 12px 22px 0;
            padding: 10px 12px;
            border-radius: 14px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #475569;
            line-height: 1.8;
        }

        .section {
            padding: 18px 22px 0;
        }

        .page-break {
            page-break-before: always;
        }

        .level-header {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: flex-start;
            padding: 12px 14px;
            border-radius: 14px;
            border: 1px solid #bfdbfe;
            background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%);
            margin-bottom: 12px;
        }

        .level-header h2 {
            font-size: 16px;
            margin-bottom: 4px;
        }

        .level-header p {
            margin: 0;
            color: #64748b;
            font-size: 11px;
        }

        .level-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 999px;
            background: #fff7ed;
            border: 1px solid #fed7aa;
            color: #9a3412;
            font-size: 12px;
            font-weight: 700;
        }

        .semester-block {
            margin-top: 10px;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            overflow: hidden;
            page-break-inside: avoid;
        }

        .semester-head {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            align-items: center;
            padding: 10px 12px;
            background: #0f172a;
            color: #ffffff;
        }

        .semester-head .title {
            font-size: 13px;
            font-weight: 700;
        }

        .semester-head .meta {
            font-size: 10px;
            color: #dbeafe;
            text-align: left;
        }

        table.courses {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        table.courses th {
            background: #1e3a8a;
            color: #ffffff;
            padding: 7px 4px;
            font-size: 10px;
            text-align: center;
        }

        table.courses td {
            border-top: 1px solid #e2e8f0;
            padding: 6px 4px;
            text-align: center;
            vertical-align: middle;
        }

        table.courses tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        table.courses td.course-name {
            text-align: right;
            padding-right: 8px;
        }

        .status {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 700;
        }

        .status.pass {
            color: #166534;
            background: #dcfce7;
        }

        .status.fail {
            color: #b45309;
            background: #ffedd5;
        }

        .status.other {
            color: #334155;
            background: #e2e8f0;
        }

        .footer {
            padding: 16px 22px 22px;
            color: #64748b;
            font-size: 10px;
            text-align: center;
        }
    </style>
</head>
<body>
@php
    $t = fn (mixed $value) => $pdfText->text($value);
    $statusLabel = function (string $status): string {
        return match ($status) {
            'Passed' => 'ناجح',
            'Failed' => 'راسب',
            default => $status ?: '-',
        };
    };

    $statusClass = function (string $status): string {
        return match ($status) {
            'Passed' => 'pass',
            'Failed' => 'fail',
            default => 'other',
        };
    };
@endphp

<div class="report">
    <div class="hero">
        <div class="hero-top">
            <div>
                <div class="header-chip">{{ $t('التقرير الأكاديمي التفصيلي') }}</div>
                <h1 class="hero-title">{{ $t('المواد المدروسة مرتبة حسب مستوى السمستر والفصل الدراسي') }}</h1>
                <div class="hero-subtitle">
                    {{ $t('يعرض هذا التقرير جميع المواد التي درسها الطالب ضمن كل مستوى دراسي ثم يرتبها داخل كل مستوى حسب الفصل الدراسي الأكاديمي، مع بيانات الدرجة والتقدير والحالة.') }}
                </div>
            </div>
            <div class="header-chip">
                {{ $t('رقم الشهادة') }}:
                <span class="ltr">{{ $record->certificate_number }}</span>
            </div>
        </div>

        <div class="student-card">
            <div class="card">
                <strong>{{ $t('اسم الطالب') }}</strong>
                {{ $t($student->full_name) }}
            </div>
            <div class="card">
                <strong>{{ $t('رقم القيد') }}</strong>
                <span class="ltr">{{ $student->registration_number }}</span>
            </div>
            <div class="card">
                <strong>{{ $t('الحالة') }}</strong>
                {{ $t($student->status) }}
            </div>
            <div class="card">
                <strong>{{ $t('القسم') }}</strong>
                {{ $t($student->currentSpecialization?->department?->name ?? '-') }}
            </div>
            <div class="card">
                <strong>{{ $t('التخصص') }}</strong>
                {{ $t($student->currentSpecialization?->name ?? '-') }}
            </div>
            <div class="card">
                <strong>{{ $t('المعدل التراكمي') }}</strong>
                <span class="ltr">{{ number_format((float) $record->cgpa, 2) }}%</span>
            </div>
        </div>

        <div class="summary-grid">
            <div class="summary-item accent-blue">
                <div class="label">{{ $t('المستويات الدراسية') }}</div>
                <div class="value">{{ $reportSummary['levels_count'] }}</div>
            </div>
            <div class="summary-item accent-orange">
                <div class="label">{{ $t('الفصول الأكاديمية') }}</div>
                <div class="value">{{ $reportSummary['semesters_count'] }}</div>
            </div>
            <div class="summary-item accent-green">
                <div class="label">{{ $t('المقررات المدروسة') }}</div>
                <div class="value">{{ $reportSummary['courses_count'] }}</div>
            </div>
            <div class="summary-item accent-slate">
                <div class="label">{{ $t('إجمالي الوحدات') }}</div>
                <div class="value">{{ $reportSummary['completed_units'] }}</div>
            </div>
        </div>

        <div class="meta-strip">
            {{ $t('تاريخ التخرج') }}:
            <span class="ltr">{{ $record->graduation_date?->format('Y-m-d') }}</span>
            &nbsp; | &nbsp;
            {{ $t('الحالة الأكاديمية') }}:
            {{ $t($eligibility['eligible'] ? 'مؤهل' : 'غير مكتمل') }}
            &nbsp; | &nbsp;
            {{ $t('تاريخ الطباعة') }}:
            <span class="ltr">{{ $printed_at->format('Y-m-d H:i') }}</span>
        </div>
    </div>

    @foreach ($studyPlanSections as $levelIndex => $levelSection)
        <div class="section {{ $levelIndex > 0 ? 'page-break' : '' }}">
            <div class="level-header">
                <div>
                    <h2>{{ $t('المستوى الدراسي') }} {{ $levelSection['level'] }}</h2>
                    <p>{{ $t('المقررات مجمعة حسب الفصل الدراسي داخل هذا المستوى') }}</p>
                </div>
                <div class="level-badge">
                    {{ $t('عدد المقررات') }}:
                    <span class="ltr">{{ $levelSection['courses_count'] }}</span>
                    &nbsp; | &nbsp;
                    {{ $t('الوحدات') }}:
                    <span class="ltr">{{ $levelSection['total_units'] }}</span>
                </div>
            </div>

            @foreach ($levelSection['semesters'] as $semesterIndex => $semester)
                <div class="semester-block">
                    <div class="semester-head">
                        <div class="title">
                            {{ $t('الفصل الدراسي') }}:
                            {{ $t($semester['label']) }}
                        </div>
                        <div class="meta">
                            {{ $t('رمز الفصل') }}:
                            <span class="ltr">{{ $semester['code'] }}</span>
                        </div>
                    </div>

                    <table class="courses">
                        <thead>
                        <tr>
                            <th style="width: 9%;">{{ $t('م') }}</th>
                            <th style="width: 13%;">{{ $t('الكود') }}</th>
                            <th style="width: 30%;">{{ $t('اسم المقرر') }}</th>
                            <th style="width: 8%;">{{ $t('الوحدات') }}</th>
                            <th style="width: 10%;">{{ $t('الأعمال') }}</th>
                            <th style="width: 10%;">{{ $t('النهائي') }}</th>
                            <th style="width: 10%;">{{ $t('المجموع') }}</th>
                            <th style="width: 10%;">{{ $t('التقدير') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($semester['courses'] as $index => $course)
                            @php
                                $courseStatus = $statusClass((string) $course['status']);
                            @endphp
                            <tr>
                                <td><span class="ltr">{{ $index + 1 }}</span></td>
                                <td><span class="ltr">{{ $course['course_code'] }}</span></td>
                                <td class="course-name">{{ $t($course['course_name']) }}</td>
                                <td><span class="ltr">{{ $course['units'] }}</span></td>
                                <td><span class="ltr">{{ $course['semester_work'] }}</span></td>
                                <td><span class="ltr">{{ $course['final_exam'] }}</span></td>
                                <td><span class="ltr">{{ $course['total_mark'] }}</span></td>
                                <td>
                                    <span class="status {{ $courseStatus }}">
                                        {{ $t($statusLabel((string) $course['status'])) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
        </div>
    @endforeach

    @if (count($eligibility['missing_courses']) > 0)
        <div class="section page-break">
            <div class="level-header">
                <div>
                    <h2>{{ $t('المقررات غير المكتملة') }}</h2>
                    <p>{{ $t('هذه المواد ما زالت مطلوبة ضمن الخطة الدراسية') }}</p>
                </div>
                <div class="level-badge">
                    {{ $t('العدد') }}:
                    <span class="ltr">{{ count($eligibility['missing_courses']) }}</span>
                </div>
            </div>

            <table class="courses">
                <thead>
                <tr>
                    <th style="width: 10%;">{{ $t('م') }}</th>
                    <th style="width: 16%;">{{ $t('الكود') }}</th>
                    <th style="width: 42%;">{{ $t('اسم المقرر') }}</th>
                    <th style="width: 12%;">{{ $t('الوحدات') }}</th>
                    <th style="width: 20%;">{{ $t('المستوى') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($eligibility['missing_courses'] as $index => $course)
                    <tr>
                        <td><span class="ltr">{{ $index + 1 }}</span></td>
                        <td><span class="ltr">{{ $course['code'] }}</span></td>
                        <td class="course-name">{{ $t($course['name']) }}</td>
                        <td><span class="ltr">{{ $course['units'] }}</span></td>
                        <td><span class="ltr">{{ $course['semester_level'] }}</span></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="footer">
        {{ $t('هذا التقرير صادر آلياً من منظومة الإدارة الأكاديمية.') }}
    </div>
</div>
</body>
</html>
