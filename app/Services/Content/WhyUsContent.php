<?php

namespace App\Services\Content;

/**
 * Text for the Why Us page and the homepage "Why choose us" cards. Each list comes
 * from a published page section in Admin › Website Content (one "- Title: text" per
 * line); until one is published, the built-in wording below is shown.
 */
class WhyUsContent
{
    public function __construct(private PageContent $content) {}

    /**
     * @return list<array{title: string, text: string}>
     */
    public function reasons(): array
    {
        return $this->content->points('why-us', 'why-different') ?: [
            ['title' => __('Honest advice'), 'text' => __('We explain what institutions and Singapore\'s authorities require, and we never promise admission or a Student\'s Pass.')],
            ['title' => __('One consultant for your file'), 'text' => __('Follow your progress, documents and messages in your own student portal.')],
            ['title' => __('Private documents'), 'text' => __('Your documents are stored privately and seen only by you and authorised staff.')],
            ['title' => __('Clear costs'), 'text' => __('We point you to official fee pages and explain every payment we receive in writing.')],
            ['title' => __('Travel help in one place'), 'text' => __('Visas, air tickets, hotels and tours are arranged by the same team that knows your plans.')],
            ['title' => __('Bangla and English support'), 'text' => __('Ask your questions in the language you are comfortable with, in person, by phone or online.')],
        ];
    }

    /**
     * What we will never do: shown as a trust box on the Why Us page.
     *
     * @return list<array{title: string, text: string}>
     */
    public function promises(): array
    {
        return $this->content->points('why-us-promises') ?: [
            ['title' => __('No guarantees we cannot keep'), 'text' => __('Admission and visa decisions belong to institutions and governments. We never sell a "guaranteed" result.')],
            ['title' => __('No false documents'), 'text' => __('We never create, alter or "arrange" documents. Every paper in your file must be genuine.')],
            ['title' => __('No hidden charges'), 'text' => __('You know what you pay us and what you pay others before you decide.')],
            ['title' => __('No pressure'), 'text' => __('A consultation is free and you decide in your own time.')],
        ];
    }

    /**
     * @return list<array{title: string, text: string}>
     */
    public function steps(): array
    {
        return $this->content->points('why-us-steps') ?: [
            ['title' => __('Free consultation'), 'text' => __('Tell us your goals, results and budget. We explain your options honestly.')],
            ['title' => __('A clear plan'), 'text' => __('You receive a written checklist of documents, costs and deadlines.')],
            ['title' => __('Application together'), 'text' => __('We prepare and review every form with you and track it in your portal.')],
            ['title' => __('Ready to travel'), 'text' => __('Ticket, accommodation guidance and a pre-departure briefing.')],
        ];
    }
}
