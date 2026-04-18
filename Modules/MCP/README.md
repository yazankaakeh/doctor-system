# MCP - AI Chat Bot Module

A comprehensive AI-powered chat bot module for Laravel that integrates with Anthropic Claude and OpenAI APIs to provide intelligent customer support for your application.

## Features

- **AI-Powered Chat**: Integrates with Anthropic Claude and OpenAI for intelligent responses
- **Morphable Relationships**: Flexible polymorphic relationships for users, doctors, patients, and guests
- **Knowledge Base**: Translatable business knowledge base with search capabilities
- **Livewire Chat Widget**: Real-time interactive chat interface
- **Multi-language Support**: Full support for English, Arabic, and Turkish
- **Feedback System**: User feedback and rating system for AI responses
- **Analytics**: Track conversation metrics, token usage, and popular questions
- **Admin Panel**: Manage knowledge base and view conversation history

## Installation

### 1. Module Setup

The module is already created in `Modules/MCP/`. No additional module installation needed.

### 2. Environment Configuration

Add the following to your `.env` file:

```env
# MCP AI Configuration
MCP_AI_PROVIDER=anthropic  # Options: anthropic, openai

# Anthropic Configuration (Recommended)
ANTHROPIC_API_KEY=your_anthropic_api_key_here
ANTHROPIC_MODEL=claude-3-5-sonnet-20241022
ANTHROPIC_MAX_TOKENS=4096
ANTHROPIC_TEMPERATURE=0.7

# OpenAI Configuration (Alternative)
OPENAI_API_KEY=your_openai_api_key_here
OPENAI_MODEL=gpt-4
OPENAI_MAX_TOKENS=2048
OPENAI_TEMPERATURE=0.7

# Chat Configuration
MCP_SESSION_TIMEOUT=30
MCP_MAX_MESSAGES=100
MCP_ENABLE_GUEST_CHAT=true
MCP_RATE_LIMIT=20

# Knowledge Base Configuration
MCP_KNOWLEDGE_SEARCH_LIMIT=5
MCP_RELEVANCE_THRESHOLD=0.7
MCP_KNOWLEDGE_CACHE_TTL=3600
```

### 3. Get API Keys

#### For Anthropic (Recommended):
1. Go to https://console.anthropic.com/
2. Sign up or log in
3. Navigate to API Keys
4. Create a new API key
5. Copy and add to `.env` as `ANTHROPIC_API_KEY`

#### For OpenAI (Alternative):
1. Go to https://platform.openai.com/
2. Sign up or log in
3. Navigate to API Keys
4. Create a new API key
5. Copy and add to `.env` as `OPENAI_API_KEY`

### 4. Run Migrations

```bash
php artisan migrate
```

This will create the following tables:
- `chat_conversations` - Stores conversation sessions
- `chat_messages` - Stores all chat messages
- `business_knowledge_base` - Stores Q&A knowledge base
- `chat_feedback` - Stores user feedback

### 5. Seed Knowledge Base

```bash
php artisan db:seed --class="Modules\MCP\Database\Seeders\BusinessKnowledgeSeeder"
```

Or seed all MCP data:

```bash
php artisan module:seed MCP
```

## Usage

### Adding Chat Widget to Your Views

Add the Livewire chat widget to any page:

```blade
@livewire('mcp::chat-widget')
```

Or in your main layout (recommended for global access):

```blade
<!-- In your footer or before </body> tag -->
@livewire('mcp::chat-widget')
```

### API Endpoints

#### Start a New Conversation
```http
POST /api/v1/chat/conversations/start
Content-Type: application/json

{
    "conversationable_type": "App\\Models\\User",
    "conversationable_id": 1,
    "metadata": {}
}
```

#### Send a Message
```http
POST /api/v1/chat/messages/send
Content-Type: application/json

{
    "conversation_id": 1,
    "message": "What are your working hours?"
}
```

#### Get Conversation by Session
```http
GET /api/v1/chat/conversations/session?session_id={session_id}
```

### Admin Panel

Access the knowledge base management:

```
/mcp/knowledge
```

Features:
- Create, edit, delete knowledge entries
- Multi-language support (EN, AR, TR)
- Category management
- Priority settings
- Usage analytics

## Knowledge Base Management

### Adding New Knowledge

1. Navigate to `/mcp/knowledge`
2. Click "Add New Knowledge"
3. Fill in the form:
   - **Category**: Main category (e.g., general, appointments, doctors)
   - **Subcategory**: Optional subcategory
   - **Question**: The question in multiple languages
   - **Answer**: The answer in multiple languages
   - **Priority**: 0-100 (higher = more important)
   - **Status**: Active/Inactive

### Categories Examples

- `general` - General information
- `appointments` - Appointment booking and management
- `doctors` - Doctor information and specialties
- `payments` - Payment methods and billing
- `insurance` - Insurance coverage
- `emergency` - Emergency services
- `contact` - Contact information

## Customization

### System Prompts

Edit `Modules/MCP/config/config.php`:

```php
'system_prompts' => [
    'default' => 'Your custom system prompt here...',
    'greeting' => 'Your custom greeting...',
    'fallback' => 'Your custom fallback message...',
],
```

### Chat Widget Styling

The chat widget uses Tailwind CSS. Customize styles in:
```
Modules/MCP/resources/views/livewire/chat-widget.blade.php
```

### AI Provider

Switch between Anthropic and OpenAI:

```env
MCP_AI_PROVIDER=anthropic  # or openai
```

## Polymorphic Relationships

The module uses Laravel's polymorphic relationships to support multiple entity types:

### Making Models Chat-Compatible

Add to any model (User, Doctor, Patient, etc.):

```php
use Modules\MCP\Models\ChatConversation;
use Modules\MCP\Models\ChatMessage;

class User extends Authenticatable
{
    public function chatConversations()
    {
        return $this->morphMany(ChatConversation::class, 'conversationable');
    }

    public function chatMessages()
    {
        return $this->morphMany(ChatMessage::class, 'sender');
    }
}
```

## Security

- Rate limiting on API endpoints
- Session-based conversation tracking
- IP address logging
- User agent tracking
- Optional authentication for admin features

## Performance

- Knowledge base caching (default: 1 hour)
- Optimized database queries
- Indexed database columns
- Token usage tracking for cost monitoring

## Troubleshooting

### Chat Widget Not Showing
- Ensure Livewire is properly installed
- Check if Alpine.js is loaded
- Verify Tailwind CSS is compiled

### AI Not Responding
- Check API key in `.env`
- Verify internet connection
- Check Laravel logs: `storage/logs/laravel.log`
- Ensure HTTP client is working

### Knowledge Base Not Searching
- Run migrations
- Seed knowledge base
- Clear cache: `php artisan cache:clear`

## Monitoring

### Token Usage

Track AI costs in the `chat_messages` table:
- `tokens_used` column shows tokens per response
- `model_used` shows which AI model was used

### Popular Questions

Check `business_knowledge_base`:
- `usage_count` shows how many times used
- `last_used_at` shows last usage timestamp

## Support

For issues or questions:
1. Check the logs: `storage/logs/laravel.log`
2. Review API documentation
3. Contact system administrator

## License

This module is part of the base project.
