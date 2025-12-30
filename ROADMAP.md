# PetFinder - Roadmap de Desenvolvimento

## ✅ Concluído

- [x] Estrutura MVC com controllers, models e views
- [x] Sistema de autenticação (login, cadastro, recuperação de senha)
- [x] Publicação e busca de anúncios de pets perdidos/encontrados
- [x] Upload de fotos com cache temporário em multi-step
- [x] Sistema de favoritos e alertas
- [x] Layout responsivo com Bootstrap
- [x] URLs amigáveis (.htaccess)
- [x] Integração PHPMailer para envio de e-mails
- [x] Páginas: perfil, meus anúncios, favoritos, busca
- [x] Validação CSRF e sanitização de inputs
- [x] Banco de dados com schema e dados iniciais

## 🚧 Em Andamento

- [ ] Testar publicação de anúncio com fotos (cache temporário)
- [ ] Testar fluxos de e-mail ponta a ponta (cadastro, recuperação)

## 📋 Próximas Implementações

### 1. Geolocalização e Mapas (Prioridade: Média)
- [ ] Integrar Google Maps API ou OpenStreetMap (Leaflet)
- [ ] Geolocalização automática por IP/CEP
- [ ] Preencher coordenadas (lat/lng) ao criar anúncio
- [ ] Mapa interativo na busca e detalhes do anúncio
- [ ] Busca por raio com visualização no mapa
- [ ] Input de endereço com autocomplete

### 2. Sistema de Doações - Efí Bank (Prioridade: Média)
- [ ] Criar `controllers/PagamentoController.php`
- [ ] Model `Doacao` com status e webhook
- [ ] View de doação com valores sugeridos
- [ ] Integração Efí Bank (PIX, cartão de crédito)
- [ ] Webhook para atualizar status após pagamento
- [ ] Página de agradecimento e comprovante
- [ ] Relatório de doações para admin

### 3. Melhorias na Busca (Prioridade: Baixa)
- [ ] Busca com sugestões automáticas (AJAX)
- [ ] Filtros avançados (idade, porte, cor, data)
- [ ] Ordenação por relevância/distância/data
- [ ] Paginação infinita ou tradicional
- [ ] Resultados em modo lista/mapa

### 4. Funcionalidades Extras (Prioridade: Baixa)
- [ ] Sistema de avaliação/confiabilidade entre usuários
- [ ] Chat interno entre dono de pet e quem encontrou
- [ ] Relatórios e estatísticas para admin
- [ ] Exportar/busca em CSV/PDF
- [ ] API REST para integração com apps
- [ ] PWA (Progressive Web App)
- [ ] Notificações push para novos pets próximos

### 5. Infraestrutura (Prioridade: Baixa)
- [ ] Cache (Redis/OPcache)
- [ ] Fila de e-mails (Redis/Beanstalk)
- [ ] Logs centralizados
- [ ] Monitoramento e health checks
- [ ] Deploy automatizado (CI/CD)

## 🐛 Bugs Conhecidos

- [ ] Validação de upload em multi-step pode avisar sobre arquivos temporários ausentes (já mitigado)
- [ ] Em dispositivos móveis, alguns botões podem precisar de ajuste de toque

## 💡 Sugestões de Melhoria

- Adicionar micro-interações e animações sutis
- Implementar dark mode
- Otimizar imagens com WebP
- Adicionar testes automatizados (PHPUnit)
- Melhorar SEO com metatags dinâmicas

---

**Nota**: Este roadmap é um guia vivo e pode ser priorizado conforme demanda dos usuários e recursos disponíveis.
