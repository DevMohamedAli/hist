<?php

return [
    'roles' => [
        'super_admin' => 'المدير العام',
        'employee' => 'موظف شؤون الطلبة',
        'teacher' => 'عضو هيئة التدريس',
        'student' => 'طالب',
    ],

    'permissions' => [
        'access student dashboard' => 'الدخول إلى لوحة الطالب',
        'access teacher dashboard' => 'الدخول إلى لوحة عضو هيئة التدريس',
        'access employee dashboard' => 'الدخول إلى لوحة الموظف',
        'manage access control' => 'إدارة الأدوار والصلاحيات',
        'manage users' => 'إدارة المستخدمين',
        'manage academic settings' => 'إدارة الإعدادات الأكاديمية',
        'manage courses' => 'إدارة المقررات',
        'manage enrollments' => 'إدارة التسجيلات الدراسية',
        'manage imports' => 'إدارة استيراد البيانات',
        'manage instructors' => 'إدارة أعضاء هيئة التدريس',
        'view activity logs' => 'عرض سجل النشاطات',
        'view students' => 'عرض بيانات الطلبة',
        'edit grades' => 'رصد وتعديل الدرجات',
    ],

    'permission_descriptions' => [
        'access student dashboard' => 'يسمح للمستخدم بفتح واجهة الطالب ومتابعة بياناته الدراسية.',
        'access teacher dashboard' => 'يسمح لعضو هيئة التدريس بفتح لوحته ومتابعة الشعب المسندة إليه.',
        'access employee dashboard' => 'يسمح للموظف بفتح لوحة الإدارة اليومية للنظام.',
        'manage access control' => 'يسمح بإنشاء الأدوار وتعديل صلاحيات المستخدمين.',
        'manage users' => 'يسمح بإدارة حسابات المستخدمين وبياناتهم الأساسية.',
        'manage academic settings' => 'يسمح بإدارة الأقسام والتخصصات والفصول والإعدادات الأكاديمية.',
        'manage courses' => 'يسمح بإنشاء وتعديل المقررات والشعب الدراسية.',
        'manage enrollments' => 'يسمح بتسجيل الطلبة في المقررات وتحديث تسجيلاتهم.',
        'manage imports' => 'يسمح باستيراد بيانات الطلبة والمقررات والدرجات من الملفات.',
        'manage instructors' => 'يسمح بإدارة بيانات المحاضرين ومؤهلاتهم وتكليفاتهم.',
        'view activity logs' => 'يسمح بعرض سجل العمليات التي تمت داخل النظام.',
        'view students' => 'يسمح بالاطلاع على ملفات الطلبة وبياناتهم الدراسية.',
        'edit grades' => 'يسمح برصد الدرجات وحفظ نتائج المقررات.',
    ],
];
