# Laravel Todo App - Security Implementation

This Laravel 12 Todo application implements robust security measures to protect against common web vulnerabilities including Cross-Site Scripting (XSS), Cross-Site Request Forgery (CSRF), and implements Content Security Policy (CSP).

## Security Features

### 1. Cross-Site Scripting (XSS) Protection

XSS protection is implemented through multiple layers:

- **Input Validation**: 
  - Form requests use regex patterns to strictly validate user input
  - Located in: `app/Http/Requests/TodoRequest.php`
  
```php
public function rules()
{
    return [
        'title' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9\s\.\,\-\_\'\"\!\?]+$/'],
        'description' => ['nullable', 'string', 'regex:/^[A-Za-z0-9\s\.\,\-\_\'\"\!\?]+$/'],
        // ...
    ];
}
```

- **Automatic Escaping**: Laravel Blade templates automatically escape output with `{{ }}` syntax
  - Example in `resources/views/todo/view.blade.php`:

```blade
<p class="form-control-static">{{ $todo->title }}</p>
<p class="form-control-static">{{ $todo->description }}</p>
```

### 2. Cross-Site Request Forgery (CSRF) Protection

CSRF protection is implemented through:

- **CSRF Tokens**: All forms include CSRF tokens via `@csrf` Blade directive
  - Example in `resources/views/todo/add.blade.php`:
  
```blade
<form method="POST" action="{{ route('todo.store') }}">
    @csrf
    <!-- Form fields -->
</form>
```

- **SameSite Cookie Attribute**: Configured to 'lax' in `config/session.php`:

```php
'same_site' => env('SESSION_SAME_SITE', 'lax'),
```

- **AJAX Protection**: JavaScript setup to include CSRF tokens in AJAX requests
  - Located in: `resources/js/bootstrap.js`
  
```javascript
const csrfMeta = document.querySelector('meta[name="csrf-token"]');
if (csrfMeta) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfMeta.getAttribute('content');
}
```

### 3. Content Security Policy (CSP)

CSP is implemented using the spatie/laravel-csp package:

- **Package Installation**:
  ```bash
  composer require spatie/laravel-csp
  ```

- **Custom Policy**: Located in `app/Policies/Csp/TodoAppCspPolicy.php`

```php
public function configure()
{
    $this
        // Base URI restriction
        ->addDirective(Directive::BASE, 'self')
        // Default sources restriction
        ->addDirective(Directive::DEFAULT, 'self')
        // Restrict connections to only our domain
        ->addDirective(Directive::CONNECT, 'self')
        // Form submissions only to our domain
        ->addDirective(Directive::FORM_ACTION, 'self')
        // Disable plugins like Flash
        ->addDirective(Directive::OBJECT, 'none')
        // Add nonce support for inline scripts
        ->addNonceForDirective(Directive::SCRIPT);
}
```

- **Configuration**: Located in `config/csp.php`
```php
'policy' => \App\Policies\Csp\TodoAppCspPolicy::class,
```

- **Middleware Registration**: In `bootstrap/app.php`
```php
$middleware->web(\Spatie\Csp\AddCspHeaders::class);
```

## How to Demo Security Features

### Testing XSS Protection

1. Try submitting a form with script tags in the Todo title:
   ```
   <script>alert('XSS')</script>
   ```
   
2. Observe one of two behaviors:
   - Input validation rejects the form submission
   - If bypassed, view the saved Todo and notice the script tags are rendered as text, not executed

### Testing CSRF Protection

1. Log in to the application
2. Using browser developer tools, try to submit a form after removing the CSRF token:
   - Inspect a form element
   - Delete the `<input name="_token" value="...">` element
   - Submit the form
   - The request will fail with a 419 status code

### Testing CSP Protection

1. Inspect page headers using browser developer tools:
   - Open Network tab
   - Load any page and check the response headers
   - Look for the Content-Security-Policy header

2. Try to execute an inline script in the browser console:
   ```javascript
   var script = document.createElement('script');
   script.innerHTML = 'alert("CSP Test")';
   document.body.appendChild(script);
   ```
   This should be blocked due to CSP restrictions

3. View the console for CSP violation messages (blocked content)

## TL;DR - How It Works

- **XSS Protection**:
  - Input validation with regex patterns blocks malicious payloads
  - Blade's {{ }} syntax automatically escapes output
  - CSP provides additional defense by restricting script sources

- **CSRF Protection**:
  - Forms automatically include @csrf tokens
  - AJAX requests include X-CSRF-TOKEN header from meta tag
  - SameSite=lax cookies prevent cross-site request forgery
  - VerifyCsrfToken middleware validates all state-changing requests

- **CSP Protection**:
  - Policy restricts content sources (scripts, styles, images, etc.)
  - Prevents execution of unauthorized/inline scripts
  - Limits communication to approved domains only
  - Provides additional defense-in-depth against XSS

p/s - with all these protections, the website is getting slower.. So if website lags or loads without styling just wait a bit

https://github.com/user-attachments/assets/09b68a68-e7f2-4c9f-9e57-db5b8e8e1598

