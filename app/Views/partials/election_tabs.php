<?php
$elections = $elections ?? [];
$election = $election ?? null;
$mode = $mode ?? (url_is('hasil*') ? 'results' : 'voting');
$currentId = (int) ($election['id'] ?? 0);

if (count($elections) > 1):
?>
    <div class="flex flex-wrap items-center gap-2">
        <?php foreach ($elections as $item): ?>
            <?php
            $active = (int) $item['id'] === $currentId;
            $href = $mode === 'results' ? election_results_url($item) : election_voting_url($item);
            ?>
            <a href="<?= esc($href) ?>" class="inline-flex items-center gap-2 rounded-full border-2 px-4 py-2 text-xs font-semibold transition <?= $active ? 'border-ink bg-ink text-sun' : 'border-ink/10 bg-paper text-ink/55 hover:border-ink/30' ?>">
                <?= icon('ballot', 'h-3.5 w-3.5') ?>
                <?= esc(election_type_label($item['type'] ?? null)) ?>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
