import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\WalletController::__invoke
* @see app/Http/Controllers/Api/WalletController.php:16
* @route '/api/wallet'
*/
const WalletController = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: WalletController.url(options),
    method: 'get',
})

WalletController.definition = {
    methods: ["get","head"],
    url: '/api/wallet',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\WalletController::__invoke
* @see app/Http/Controllers/Api/WalletController.php:16
* @route '/api/wallet'
*/
WalletController.url = (options?: RouteQueryOptions) => {
    return WalletController.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\WalletController::__invoke
* @see app/Http/Controllers/Api/WalletController.php:16
* @route '/api/wallet'
*/
WalletController.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: WalletController.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\WalletController::__invoke
* @see app/Http/Controllers/Api/WalletController.php:16
* @route '/api/wallet'
*/
WalletController.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: WalletController.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Api\WalletController::__invoke
* @see app/Http/Controllers/Api/WalletController.php:16
* @route '/api/wallet'
*/
const WalletControllerForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: WalletController.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\WalletController::__invoke
* @see app/Http/Controllers/Api/WalletController.php:16
* @route '/api/wallet'
*/
WalletControllerForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: WalletController.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Api\WalletController::__invoke
* @see app/Http/Controllers/Api/WalletController.php:16
* @route '/api/wallet'
*/
WalletControllerForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: WalletController.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

WalletController.form = WalletControllerForm

export default WalletController