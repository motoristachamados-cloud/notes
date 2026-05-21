import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\PaymentsController::webhook
* @see app/Http/Controllers/Api/PaymentsController.php:37
* @route '/api/payments/webhook'
*/
export const webhook = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: webhook.url(options),
    method: 'post',
})

webhook.definition = {
    methods: ["post"],
    url: '/api/payments/webhook',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\PaymentsController::webhook
* @see app/Http/Controllers/Api/PaymentsController.php:37
* @route '/api/payments/webhook'
*/
webhook.url = (options?: RouteQueryOptions) => {
    return webhook.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\PaymentsController::webhook
* @see app/Http/Controllers/Api/PaymentsController.php:37
* @route '/api/payments/webhook'
*/
webhook.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: webhook.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Api\PaymentsController::webhook
* @see app/Http/Controllers/Api/PaymentsController.php:37
* @route '/api/payments/webhook'
*/
const webhookForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: webhook.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Api\PaymentsController::webhook
* @see app/Http/Controllers/Api/PaymentsController.php:37
* @route '/api/payments/webhook'
*/
webhookForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: webhook.url(options),
    method: 'post',
})

webhook.form = webhookForm

/**
* @see \App\Http\Controllers\Api\PaymentsController::create
* @see app/Http/Controllers/Api/PaymentsController.php:19
* @route '/api/payments/create'
*/
export const create = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: create.url(options),
    method: 'post',
})

create.definition = {
    methods: ["post"],
    url: '/api/payments/create',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\PaymentsController::create
* @see app/Http/Controllers/Api/PaymentsController.php:19
* @route '/api/payments/create'
*/
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\PaymentsController::create
* @see app/Http/Controllers/Api/PaymentsController.php:19
* @route '/api/payments/create'
*/
create.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: create.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Api\PaymentsController::create
* @see app/Http/Controllers/Api/PaymentsController.php:19
* @route '/api/payments/create'
*/
const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: create.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Api\PaymentsController::create
* @see app/Http/Controllers/Api/PaymentsController.php:19
* @route '/api/payments/create'
*/
createForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: create.url(options),
    method: 'post',
})

create.form = createForm

const PaymentsController = { webhook, create }

export default PaymentsController