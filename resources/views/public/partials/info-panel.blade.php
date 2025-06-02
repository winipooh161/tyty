<!-- Маленькая информационная панель, которую можно скрыть -->
<div class="info-panel" id="infoPanel">
    <span>{{ $userTemplate->name }}</span>
    @auth
        <a href="{{ route('client.templates.editor', $userTemplate->template_id) }}" class="btn-use">Использовать шаблон</a>
    @else
        <a href="{{ route('login') }}" class="btn-use">Войти для использования</a>
    @endauth
    <span class="close-panel" onclick="togglePanel()">&times;</span>
</div>

<!-- Кнопка для отображения панели -->
<div class="toggle-panel" id="togglePanel" onclick="togglePanel()">
    <i class="bi bi-info"></i>
</div>
