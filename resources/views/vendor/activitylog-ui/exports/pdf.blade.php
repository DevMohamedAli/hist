<!DOCTYPE html>
<html dir="rtl" lang="ar">

<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Cairo', 'DejaVu Sans', 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #1f2937;
            background: #fff;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #f97316;
            padding-bottom: 15px;
        }

        .header h1 {
            margin: 0;
            font-size: 22px;
            color: #1e40af;
            font-weight: 700;
        }

        .header .subtitle {
            font-size: 11px;
            color: #6b7280;
            margin-top: 5px;
        }

        .meta-info {
            margin-bottom: 20px;
            background: #f3f4f6;
            padding: 12px;
            border-radius: 8px;
            border-right: 4px solid #f97316;
        }

        .meta-info table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta-info td {
            padding: 6px 8px;
            vertical-align: top;
        }

        .meta-info .label {
            font-weight: 700;
            width: 130px;
            color: #374151;
        }

        .meta-info .value {
            color: #111827;
        }

        .activities-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 11px;
        }

        .activities-table th,
        .activities-table td {
            border: 1px solid #e5e7eb;
            padding: 10px 8px;
            text-align: right;
            vertical-align: top;
        }

        .activities-table th {
            background-color: #f9fafb;
            font-weight: 700;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #374151;
        }

        .activities-table td {
            color: #1f2937;
        }

        .event-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 9px;
            font-weight: 700;
        }

        .event-created {
            background-color: #d1fae5;
            color: #065f46;
        }

        .event-updated {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .event-deleted {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .event-restored {
            background-color: #fef3c7;
            color: #92400e;
        }

        .event-login {
            background-color: #e9d5ff;
            color: #5b21b6;
        }

        .event-logout {
            background-color: #e9d5ff;
            color: #5b21b6;
        }

        .event-system {
            background-color: #f3e8ff;
            color: #6b21a5;
        }

        .event-default {
            background-color: #e5e7eb;
            color: #374151;
        }

        .changes-summary {
            font-size: 9px;
            color: #6b7280;
            font-style: italic;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
        }

        .page-break {
            page-break-after: always;
        }

        @media print {
            body {
                padding: 0;
            }

            .meta-info {
                background: #f3f4f6;
            }

            .header {
                border-bottom-color: #f97316;
            }
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <div class="subtitle">المعهد العالي للعلوم والتقنية - العجيلات</div>
    </div>

    <div class="meta-info">
        <table>
            <tr>
                <td class="label">تاريخ التقرير:</td>
                <td class="value">{{ $generated_at->format('Y-m-d g:i A') }}</td>
            </tr>
            <tr>
                <td class="label">إجمالي السجلات:</td>
                <td class="value">{{ number_format($total_count) }}</td>
            </tr>
            @if(!empty($filters))
            <tr>
                <td class="label">الفلاتر المطبقة:</td>
                <td class="value">
                    @foreach($filters as $key => $value)
                    @if($value && (!is_array($value) || count($value) > 0))
                    <strong>{{ str_replace('_', ' ', ucfirst($key)) }}:</strong>
                    {{ is_array($value) ? implode(', ', $value) : $value }}
                    @if(!$loop->last) &nbsp;|&nbsp; @endif
                    @endif
                    @endforeach
                </td>
            </tr>
            @endif
        </table>
    </div>

    <table class="activities-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 15%;">التاريخ والوقت</th>
                <th style="width: 12%;">المستخدم</th>
                <th style="width: 10%;">الحدث</th>
                <th style="width: 13%;">الموضوع</th>
                <th style="width: 35%;">الوصف</th>
                <th style="width: 10%;">التغييرات</th>
            </tr>
        </thead>
        <tbody>
            @forelse($activities as $index => $activity)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $activity->created_at->format('Y-m-d H:i') }}</td>
                <td>{{ $activity->causer_name ?? 'النظام' }}</td>
                <td>
                    <span class="event-badge event-{{ $activity->event ?? 'default' }}">
                        {{ $activity->event ?? 'غير معروف' }}
                    </span>
                </td>
                <td>
                    @if($activity->subject_type)
                    {{ class_basename($activity->subject_type) }} #{{ $activity->subject_id }}
                    @else
                    —
                    @endif
                </td>
                <td>{{ $activity->description }}</td>
                <td class="changes-summary">
                    @if($activity->hasAttributeChanges())
                    {{ $activity->getChangesSummary() }}
                    @else
                    —
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; padding: 40px; color: #6b7280;">
                    لا توجد نشاطات تطابق المعايير المحددة.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>تم إنشاء هذا التقرير بواسطة نظام سجل النشاطات – المعهد العالي للعلوم والتقنية<br>
            يحتوي التقرير على {{ number_format($total_count) }} سجل نشاط. تاريخ الإنشاء: {{ $generated_at->format('Y-m-d g:i A') }}</p>
    </div>
</body>

</html>