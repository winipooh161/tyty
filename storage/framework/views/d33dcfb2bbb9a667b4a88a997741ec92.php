<!doctype html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo e(config('app.name', 'Laravel')); ?></title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Bootstrap CSS и JS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Локальные стили вместо использования Vite -->
    <link href="<?php echo e(asset('css/app.css')); ?>" rel="stylesheet">
      <!-- Vite ресурсы отключены, используйте прямые ссылки на файлы -->
    <!-- Дополнительные стили и скрипты -->
    <?php echo $__env->yieldContent('styles'); ?>
</head>
<body>
    <div id="app" class="d-flex">
        <?php if(auth()->guard()->check()): ?>
            <!-- Подключение боковой панели навигации для ПК (скрываем на странице редактора) -->
            <?php if(!request()->routeIs('client.templates.editor')): ?>
                <?php echo $__env->make('layouts.partials.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <?php endif; ?>
            
            <!-- Подключение мобильной навигации (скрываем на странице редактора) -->
            <?php if(!request()->routeIs('client.templates.editor')): ?>
                <?php echo $__env->make('layouts.partials.mobile-nav', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <?php endif; ?>
        <?php endif; ?>
        
        <main class="py-4 flex-grow-1 content-wrapper <?php echo e(request()->routeIs('client.templates.editor') ? 'p-0' : ''); ?>">
            <?php echo $__env->yieldContent('content'); ?>
        </main>
    </div>
    
    <!-- Axios для AJAX-запросов -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    
    <!-- Скрипт для автоматического обновления CSRF токена -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Функция для обновления CSRF токена
        function refreshCsrfToken() {
            return axios.get('<?php echo e(route('refresh-csrf')); ?>')
                .then(function(response) {
                    if (response.data && response.data.token) {
                        // Обновляем токен в мета-теге
                        const tokenElement = document.querySelector('meta[name="csrf-token"]');
                        if (tokenElement) {
                            tokenElement.setAttribute('content', response.data.token);
                        }
                        
                        // Обновляем токен во всех формах
                        document.querySelectorAll('input[name="_token"]').forEach(input => {
                            input.value = response.data.token;
                        });
                        
                        // Обновляем заголовок для Axios
                        if (window.axios) {
                            window.axios.defaults.headers.common['X-CSRF-TOKEN'] = response.data.token;
                        }
                        
                        console.log('CSRF токен успешно обновлен');
                        return response.data.token;
                    }
                })
                .catch(function(error) {
                    console.error('Не удалось обновить CSRF токен:', error);
                });
        }
        
        // Настраиваем перехватчик для Axios
        axios.interceptors.response.use(
            response => response,
            error => {
                // Определяем ошибку CSRF токена
                const isCsrfError = error.response && 
                    (error.response.status === 419 || 
                    (error.response.status === 422 && error.response.data.message && 
                     error.response.data.message.includes('CSRF')));
                
                if (isCsrfError) {
                    // Если это ошибка CSRF, обновляем токен и повторяем запрос
                    return refreshCsrfToken().then(() => {
                        // Создаем новый экземпляр запроса с обновленным токеном
                        const config = error.config;
                        
                        // Если это POST, PUT или DELETE запрос, обновляем токен в теле запроса
                        if (['post', 'put', 'patch', 'delete'].includes(config.method.toLowerCase()) && config.data) {
                            try {
                                let data = config.data;
                                
                                // Если это FormData
                                if (config.data instanceof FormData) {
                                    // Удаляем старый токен и добавляем новый
                                    config.data.delete('_token');
                                    config.data.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                                }
                                // Если это строка (например, сериализованная форма)
                                else if (typeof config.data === 'string') {
                                    // Заменяем старый токен на новый
                                    let newToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                                    config.data = config.data.replace(/_token=[^&]+/, '_token=' + newToken);
                                }
                                // Если это объект JSON
                                else if (typeof config.data === 'object') {
                                    let data = JSON.parse(config.data);
                                    data._token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                                    config.data = JSON.stringify(data);
                                }
                            } catch (e) {
                                console.error('Ошибка при обновлении токена в запросе:', e);
                            }
                        }
                        
                        // Повторяем исходный запрос с обновленным токеном
                        return axios(config);
                    });
                }
                
                // Для других ошибок просто возвращаем их
                return Promise.reject(error);
            }
        );
        
        // Устанавливаем обработчики для стандартных fetch-запросов
        const originalFetch = window.fetch;
        window.fetch = function(url, options = {}) {
            return originalFetch(url, options).then(response => {
                if (response.status === 419) {
                    // Если ошибка CSRF, обновляем токен и повторяем запрос
                    return refreshCsrfToken().then(token => {
                        // Создаем новые опции с обновленным токеном
                        const newOptions = {...options};
                        
                        // Обновляем заголовки
                        if (!newOptions.headers) {
                            newOptions.headers = {};
                        }
                        
                        // Обновляем заголовок X-CSRF-TOKEN
                        newOptions.headers['X-CSRF-TOKEN'] = token;
                        
                        // Если это запрос с телом, обновляем токен в теле
                        if (newOptions.body) {
                            try {
                                if (newOptions.body instanceof FormData) {
                                    newOptions.body.delete('_token');
                                    newOptions.body.append('_token', token);
                                }
                            } catch (e) {
                                console.error('Ошибка при обновлении токена в fetch-запросе:', e);
                            }
                        }
                        
                        // Повторяем запрос с обновленным токеном
                        return originalFetch(url, newOptions);
                    });
                }
                return response;
            });
        };
        
        // Запускаем периодическую проверку и обновление токена каждые 55 минут
        // (стандартное время жизни сессии Laravel - 2 часа, обновляем за 5 минут до истечения)
        setInterval(refreshCsrfToken, 55 * 60 * 1000);
    });
    </script>
    
    <!-- Дополнительные скрипты внизу страницы -->
    <script src="<?php echo e(asset('js/app.js')); ?>"></script>
    <?php echo $__env->yieldContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\OSPanel\domains\tyty\resources\views/layouts/app.blade.php ENDPATH**/ ?>