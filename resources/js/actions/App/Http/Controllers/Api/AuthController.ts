import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\AuthController::google
* @see app/Http/Controllers/Api/AuthController.php:17
* @route '/api/auth/google'
*/
export const google = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: google.url(options),
    method: 'post',
})

google.definition = {
    methods: ["post"],
    url: '/api/auth/google',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\AuthController::google
* @see app/Http/Controllers/Api/AuthController.php:17
* @route '/api/auth/google'
*/
google.url = (options?: RouteQueryOptions) => {
    return google.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\AuthController::google
* @see app/Http/Controllers/Api/AuthController.php:17
* @route '/api/auth/google'
*/
google.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: google.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Api\AuthController::google
* @see app/Http/Controllers/Api/AuthController.php:17
* @route '/api/auth/google'
*/
const googleForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: google.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Api\AuthController::google
* @see app/Http/Controllers/Api/AuthController.php:17
* @route '/api/auth/google'
*/
googleForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: google.url(options),
    method: 'post',
})

google.form = googleForm

const AuthController = { google }

export default AuthController