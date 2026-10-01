<?php
$editing = !empty($editing_event) ? $editing_event : null;
$datetime_value = function ($value) { return $value ? date('Y-m-d\\TH:i', strtotime($value)) : ''; };
$format_event_range = function ($event) {
    $start = strtotime($event->starts_at);
    $end = strtotime($event->ends_at);
    if (date('Y-m-d', $start) === date('Y-m-d', $end)) {
        return date('M j, Y', $start) . ' - ' . date('g:i A', $start) . ' - ' . date('g:i A', $end);
    }
    return date('M j, Y g:i A', $start) . ' - ' . date('M j, Y g:i A', $end);
};
?>
<?php if (!empty($error_message)): ?><div class="alert alert-danger shadow-sm"><?= html_escape($error_message); ?></div><?php endif; ?>
<?php if (!empty($success_message)): ?><div class="alert alert-success shadow-sm"><?= html_escape($success_message); ?></div><?php endif; ?>

<section class="events-hero mb-4">
    <div class="events-hero-icon"><i class="fas fa-calendar-days" aria-hidden="true"></i></div>
    <div class="events-hero-copy">
        <span class="events-hero-eyebrow">School calendar</span>
        <h1><?= $editing ? 'Update an event' : 'Make it a date to remember'; ?></h1>
        <p>Keep staff up to date with school activities, celebrations, and important dates.</p>
    </div>
    <?php if (!empty($can_manage_events)): ?><a class="btn events-hero-button" href="<?= $editing ? site_url('events') : '#event-form'; ?>"><i class="fas <?= $editing ? 'fa-arrow-left' : 'fa-plus'; ?> me-2" aria-hidden="true"></i><?= $editing ? 'Back to events' : 'Add an event'; ?></a><?php endif; ?>
</section>

<div class="row g-4 align-items-start">
    <?php if (!empty($can_manage_events)): ?>
    <div class="col-xl-4">
        <section class="card event-form-card" id="event-form">
            <div class="event-form-heading">
                <span class="event-form-icon"><i class="fas <?= $editing ? 'fa-pen-to-square' : 'fa-wand-magic-sparkles'; ?>" aria-hidden="true"></i></span>
                <div><h2><?= $editing ? 'Edit event details' : 'Create an event'; ?></h2><p>Share the details with your school community.</p></div>
            </div>
            <form method="post" action="<?= site_url('events/save'); ?>">
                <input type="hidden" name="<?= html_escape($this->security->get_csrf_token_name()); ?>" value="<?= html_escape($this->security->get_csrf_hash()); ?>">
                <?php if ($editing): ?><input type="hidden" name="id" value="<?= (int) $editing->id; ?>"><?php endif; ?>
                <div class="card-body event-form-body">
                    <div class="mb-3"><label class="form-label" for="event-title">Event name <span class="text-danger">*</span></label><input class="form-control" id="event-title" name="title" maxlength="160" placeholder="e.g. School Foundation Day" required value="<?= html_escape(set_value('title', $editing->title ?? '')); ?>"></div>
                    <div class="row g-3 mb-3">
                        <div class="col-12"><label class="form-label" for="event-start">Starts <span class="text-danger">*</span></label><input class="form-control" type="datetime-local" id="event-start" name="starts_at" required value="<?= html_escape(set_value('starts_at', $datetime_value($editing->starts_at ?? null))); ?>"></div>
                        <div class="col-12"><label class="form-label" for="event-end">Ends <span class="text-danger">*</span></label><input class="form-control" type="datetime-local" id="event-end" name="ends_at" required value="<?= html_escape(set_value('ends_at', $datetime_value($editing->ends_at ?? null))); ?>"></div>
                    </div>
                    <div class="mb-3"><label class="form-label" for="event-location">Location <span class="text-secondary fw-normal">(optional)</span></label><div class="input-group"><span class="input-group-text"><i class="fas fa-location-dot" aria-hidden="true"></i></span><input class="form-control" id="event-location" name="location" maxlength="180" placeholder="Where will it happen?" value="<?= html_escape(set_value('location', $editing->location ?? '')); ?>"></div></div>
                    <div class="mb-0"><label class="form-label" for="event-description">Description <span class="text-secondary fw-normal">(optional)</span></label><textarea class="form-control" id="event-description" name="description" rows="3" maxlength="2000" placeholder="Add useful details for attendees"><?= html_escape(set_value('description', $editing->description ?? '')); ?></textarea></div>
                    <p class="event-form-hint"><i class="fas fa-circle-info me-1" aria-hidden="true"></i>This event will appear on staff dashboards and the school events page.</p>
                </div>
                <div class="card-footer event-form-footer">
                    <?php if ($editing): ?><a class="btn btn-outline-secondary" href="<?= site_url('events'); ?>">Cancel</a><?php endif; ?>
                    <button class="btn btn-primary event-submit-button" type="submit"><i class="fas fa-check me-2" aria-hidden="true"></i><?= $editing ? 'Save changes' : 'Create event'; ?></button>
                </div>
            </form>
        </section>
    </div>
    <?php endif; ?>

    <div class="<?= !empty($can_manage_events) ? 'col-xl-8' : 'col-12'; ?>">
        <section class="events-section mb-4">
            <div class="events-section-heading">
                <div><span class="events-section-kicker">Coming up</span><h2>Upcoming events</h2></div>
                <span class="events-count-pill"><?= number_format(count($upcoming_events)); ?> scheduled</span>
            </div>
            <?php if (empty($upcoming_events)): ?>
                <div class="event-empty-card"><span class="event-empty-icon"><i class="fas fa-calendar-plus" aria-hidden="true"></i></span><h3>No upcoming events</h3><p><?= !empty($can_manage_events) ? 'Add an event to let everyone know what is happening.' : 'Check back soon for school activities and announcements.'; ?></p></div>
            <?php else: ?>
                <div class="school-event-list">
                    <?php foreach ($upcoming_events as $event): $startTs = strtotime($event->starts_at); $isHappening = $startTs <= time(); ?>
                        <article class="school-event-card">
                            <div class="school-event-date"><span><?= html_escape(date('M', $startTs)); ?></span><strong><?= html_escape(date('d', $startTs)); ?></strong><small><?= html_escape(date('D', $startTs)); ?></small></div>
                            <div class="school-event-content">
                                <div class="school-event-title-row"><h3><?= html_escape($event->title); ?></h3><span class="school-event-status <?= $isHappening ? 'is-live' : ''; ?>"><?= $isHappening ? 'Happening now' : 'Upcoming'; ?></span></div>
                                <div class="school-event-meta"><span><i class="far fa-clock" aria-hidden="true"></i><?= html_escape($format_event_range($event)); ?></span><?php if (!empty($event->location)): ?><span><i class="fas fa-location-dot" aria-hidden="true"></i><?= html_escape($event->location); ?></span><?php endif; ?></div>
                                <?php if (!empty($event->description)): ?><p class="school-event-description"><?= nl2br(html_escape($event->description)); ?></p><?php endif; ?>
                            </div>
                            <?php if (!empty($can_manage_events)): ?><div class="school-event-actions"><a class="btn btn-sm btn-light" href="<?= site_url('events/edit/' . (int) $event->id); ?>" aria-label="Edit <?= html_escape($event->title); ?>"><i class="fas fa-pen" aria-hidden="true"></i></a><form method="post" action="<?= site_url('events/cancel/' . (int) $event->id); ?>" onsubmit="return confirm('Cancel this event?');"><input type="hidden" name="<?= html_escape($this->security->get_csrf_token_name()); ?>" value="<?= html_escape($this->security->get_csrf_hash()); ?>"><button class="btn btn-sm btn-light text-danger" type="submit" aria-label="Cancel <?= html_escape($event->title); ?>"><i class="fas fa-xmark" aria-hidden="true"></i></button></form></div><?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <section class="completed-events-panel">
            <div class="completed-events-heading"><div><span class="events-section-kicker">A look back</span><h2>Completed events</h2></div><span class="completed-events-icon"><i class="fas fa-check" aria-hidden="true"></i></span></div>
            <?php if (empty($completed_events)): ?><div class="completed-events-empty">Completed school events will be saved here.</div>
            <?php else: ?><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Event</th><th>Date</th><th>Location</th></tr></thead><tbody>
                <?php foreach ($completed_events as $event): ?><tr><td><div class="fw-semibold"><?= html_escape($event->title); ?></div><?php if ($event->description): ?><small class="text-secondary"><?= html_escape($event->description); ?></small><?php endif; ?></td><td><?= html_escape($format_event_range($event)); ?></td><td><?= $event->location ? html_escape($event->location) : '&mdash;'; ?></td></tr><?php endforeach; ?>
            </tbody></table></div><?php endif; ?>
        </section>
    </div>
</div>
