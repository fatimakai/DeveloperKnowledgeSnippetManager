@component('mail::message')
# Welcome to Developer Knowledge Snippet Manager

Hi {{ $user->name }},

Welcome to the Developer Knowledge Snippet Manager! We're excited to have you on board.

This platform allows you to:
- **Create & Manage Snippets** – Write, edit, and organize your code snippets
- **Smart Tagging** – Categorize snippets with tags for easy organization
- **Advanced Filtering** – Search by title, language, tags, and visibility
- **Public & Private** – Share snippets publicly or keep them private
- **Export Options** – Download snippets as JSON or PDF

## Getting Started

1. Navigate to your dashboard
2. Click "Create Snippet" to add your first code snippet
3. Use tags to organize your snippets
4. Share your public snippets with the community

## Need Help?

If you have any questions, feel free to reach out. We're here to help!

Best regards,  
**The Developer Knowledge Snippet Manager Team**

@component('mail::subcopy')
If you have any questions about the platform, please don't hesitate to get in touch.
@endcomponent
@endcomponent
