import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\DownloadController::xml
* @see app/Http/Controllers/Api/DownloadController.php:23
* @route '/download/xml/{access_key}'
*/
export const xml = (args: { access_key: string | number } | [access_key: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: xml.url(args, options),
    method: 'get',
})

xml.definition = {
    methods: ["get","head"],
    url: '/download/xml/{access_key}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\DownloadController::xml
* @see app/Http/Controllers/Api/DownloadController.php:23
* @route '/download/xml/{access_key}'
*/
xml.url = (args: { access_key: string | number } | [access_key: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { access_key: args }
    }

    if (Array.isArray(args)) {
        args = {
            access_key: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        access_key: args.access_key,
    }

    return xml.definition.url
            .replace('{access_key}', parsedArgs.access_key.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\DownloadController::xml
* @see app/Http/Controllers/Api/DownloadController.php:23
* @route '/download/xml/{access_key}'
*/
xml.get = (args: { access_key: string | number } | [access_key: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: xml.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\DownloadController::xml
* @see app/Http/Controllers/Api/DownloadController.php:23
* @route '/download/xml/{access_key}'
*/
xml.head = (args: { access_key: string | number } | [access_key: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: xml.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Api\DownloadController::xml
* @see app/Http/Controllers/Api/DownloadController.php:23
* @route '/download/xml/{access_key}'
*/
const xmlForm = (args: { access_key: string | number } | [access_key: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: xml.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\DownloadController::xml
* @see app/Http/Controllers/Api/DownloadController.php:23
* @route '/download/xml/{access_key}'
*/
xmlForm.get = (args: { access_key: string | number } | [access_key: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: xml.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\DownloadController::xml
* @see app/Http/Controllers/Api/DownloadController.php:23
* @route '/download/xml/{access_key}'
*/
xmlForm.head = (args: { access_key: string | number } | [access_key: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: xml.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

xml.form = xmlForm

/**
* @see \App\Http\Controllers\Api\DownloadController::pdf
* @see app/Http/Controllers/Api/DownloadController.php:28
* @route '/download/pdf/{access_key}'
*/
export const pdf = (args: { access_key: string | number } | [access_key: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: pdf.url(args, options),
    method: 'get',
})

pdf.definition = {
    methods: ["get","head"],
    url: '/download/pdf/{access_key}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\DownloadController::pdf
* @see app/Http/Controllers/Api/DownloadController.php:28
* @route '/download/pdf/{access_key}'
*/
pdf.url = (args: { access_key: string | number } | [access_key: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { access_key: args }
    }

    if (Array.isArray(args)) {
        args = {
            access_key: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        access_key: args.access_key,
    }

    return pdf.definition.url
            .replace('{access_key}', parsedArgs.access_key.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\DownloadController::pdf
* @see app/Http/Controllers/Api/DownloadController.php:28
* @route '/download/pdf/{access_key}'
*/
pdf.get = (args: { access_key: string | number } | [access_key: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: pdf.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\DownloadController::pdf
* @see app/Http/Controllers/Api/DownloadController.php:28
* @route '/download/pdf/{access_key}'
*/
pdf.head = (args: { access_key: string | number } | [access_key: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: pdf.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Api\DownloadController::pdf
* @see app/Http/Controllers/Api/DownloadController.php:28
* @route '/download/pdf/{access_key}'
*/
const pdfForm = (args: { access_key: string | number } | [access_key: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: pdf.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\DownloadController::pdf
* @see app/Http/Controllers/Api/DownloadController.php:28
* @route '/download/pdf/{access_key}'
*/
pdfForm.get = (args: { access_key: string | number } | [access_key: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: pdf.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\DownloadController::pdf
* @see app/Http/Controllers/Api/DownloadController.php:28
* @route '/download/pdf/{access_key}'
*/
pdfForm.head = (args: { access_key: string | number } | [access_key: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: pdf.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

pdf.form = pdfForm

/**
* @see \App\Http\Controllers\Api\DownloadController::result
* @see app/Http/Controllers/Api/DownloadController.php:81
* @route '/download/result/{token}'
*/
export const result = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: result.url(args, options),
    method: 'get',
})

result.definition = {
    methods: ["get","head"],
    url: '/download/result/{token}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\DownloadController::result
* @see app/Http/Controllers/Api/DownloadController.php:81
* @route '/download/result/{token}'
*/
result.url = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { token: args }
    }

    if (Array.isArray(args)) {
        args = {
            token: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        token: args.token,
    }

    return result.definition.url
            .replace('{token}', parsedArgs.token.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\DownloadController::result
* @see app/Http/Controllers/Api/DownloadController.php:81
* @route '/download/result/{token}'
*/
result.get = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: result.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\DownloadController::result
* @see app/Http/Controllers/Api/DownloadController.php:81
* @route '/download/result/{token}'
*/
result.head = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: result.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Api\DownloadController::result
* @see app/Http/Controllers/Api/DownloadController.php:81
* @route '/download/result/{token}'
*/
const resultForm = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: result.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\DownloadController::result
* @see app/Http/Controllers/Api/DownloadController.php:81
* @route '/download/result/{token}'
*/
resultForm.get = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: result.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\DownloadController::result
* @see app/Http/Controllers/Api/DownloadController.php:81
* @route '/download/result/{token}'
*/
resultForm.head = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: result.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

result.form = resultForm

const DownloadController = { xml, pdf, result }

export default DownloadController