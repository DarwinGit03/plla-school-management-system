<section class="events-preview-card">
    <header class="events-preview-header">
        <div class="events-preview-title">
            <span class="events-preview-title-icon"><i class="fas fa-calendar-days" aria-hidden="true"></i></span>
            <div><h2>Upcoming events</h2><p>School activities coming up.</p></div>
        </div>
        <a class="events-preview-link" href="<?= site_url('events'); ?>">See all events <i class="fas fa-arrow-right ms-1" aria-hidden="true"></i></a>
    </header>
    <?php if (empty($upcoming_events)): ?>
        <div class="events-preview-empty">No upcoming events scheduled.</div>
    <?php else: ?>
        <div class="events-preview-list">
            <?php foreach (array_slice($upcoming_events, 0, 5) as $event): $eventStart = strtotime($event->starts_at); ?>
                <article class="events-preview-item">
                    <span class="events-preview-date"><span><?= html_escape(date('M', $eventStart)); ?></span><strong><?= html_escape(date('d', $eventStart)); ?></strong></span>
                    <span class="events-preview-copy"><strong><?= html_escape($event->title); ?></strong><small><?= html_escape(date('g:i A', $eventStart)); ?><?php if (!empty($event->location)): ?> | <?= html_escape($event->location); ?><?php endif; ?></small></span>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
