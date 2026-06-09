<!DOCTYPE html>
<html dir="rtl" lang="ar">

<head>
    <meta charset="UTF-8">
    <title>سجل النشاطات</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: right;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>
    <h2>سجل النشاطات</h2>
    <p>تاريخ التقرير: {{ $generated_at->format('Y-m-d H:i') }}</p>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>الحدث</th>
                <th>الوصف</th>
                <th>المستخدم</th>
                <th>الموضوع</th>
                <th>المعرف</th>
                <th>التاريخ</th>
            </tr>
        </thead>
        <tbody>
            @foreach($activities as $i => $activity)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $activity->event }}</td>
                <td>{{ $activity->description }}</td>
                <td>{{ $activity->causer?->name ?? 'النظام' }}</td>
                <td>{{ $activity->subject_type ? class_basename($activity->subject_type) : '' }}</td>
                <td>{{ $activity->subject_id }}</td>
                <td>{{ $activity->created_at->format('Y-m-d H:i:s') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>