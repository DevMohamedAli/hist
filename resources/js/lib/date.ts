type DateInput = string | number | Date | null | undefined

const dateLocale = 'ar-LY-u-nu-latn'

const parseDate = (value: DateInput): Date | null => {
    if (value === null || value === undefined || value === '') {
        return null
    }

    const date = value instanceof Date ? value : new Date(value)

    return Number.isNaN(date.getTime()) ? null : date
}

export const formatDisplayDate = (value: DateInput, fallback = '-'): string => {
    const date = parseDate(value)

    if (!date) {
        return fallback
    }

    return new Intl.DateTimeFormat(dateLocale, {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    }).format(date)
}

export const formatDisplayDateTime = (value: DateInput, fallback = '-'): string => {
    const date = parseDate(value)

    if (!date) {
        return fallback
    }

    return new Intl.DateTimeFormat(dateLocale, {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    }).format(date)
}

export const formatRelativeDateTime = (value: DateInput, fallback = '-'): string => {
    const date = parseDate(value)

    if (!date) {
        return fallback
    }

    const diffMs = Date.now() - date.getTime()
    const diffMins = Math.floor(diffMs / 60000)
    const diffHours = Math.floor(diffMs / 3600000)
    const diffDays = Math.floor(diffMs / 86400000)

    if (diffMins < 1) {
        return 'الآن'
    }

    if (diffMins < 60) {
        return `منذ ${diffMins} دقيقة`
    }

    if (diffHours < 24) {
        return `منذ ${diffHours} ساعة`
    }

    if (diffDays === 1) {
        return 'أمس'
    }

    return formatDisplayDateTime(date, fallback)
}
