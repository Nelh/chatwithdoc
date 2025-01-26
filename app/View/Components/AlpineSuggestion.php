<?php

namespace App\View\Components;

use Illuminate\View\Component;

class AlpineSuggestion extends Component
{
    public $suggestions = [];
    public bool $chats;

    public function __construct($chats = false)
    {
        $this->chats = $chats;
        $this->suggestions = $this->getInitialSuggestions();
    }

    public function getInitialSuggestions()
    {
        $allSuggestions = $this->chats ? $this->conversations() : $this->writing();
        return collect($allSuggestions)
            ->shuffle()
            ->take(5)
            ->toArray();
    }

    public function writing()
    {
        return [
            // Contract Structure
            'Draft a standard opening clause',
            'Create a clear definitions section',
            'Write a scope of work section',
            'Draft payment terms and conditions',
            'Create a termination clause',
            'Write a confidentiality agreement section',
            'Draft dispute resolution procedures',
            'Create intellectual property rights clause',
            'Write indemnification provisions',
            'Draft force majeure clause',
            'Create warranties and representations',
            'Write liability limitations',
            'Draft amendment procedures',
            'Create notice requirements section',
            'Write governing law clause',

            // Document Types
            'Create an executive summary',
            'Draft a project proposal',
            'Write a statement of work',
            'Create a service level agreement',
            'Draft a non-disclosure agreement',
            'Write an employment contract',
            'Create a purchase agreement',
            'Draft a partnership agreement',
            'Write a lease agreement',
            'Create a consulting contract',
            'Draft a software license agreement',
            'Write a distribution agreement',
            'Create a franchise agreement',
            'Draft a settlement agreement',
            'Write a loan agreement',

            // Terms & Conditions
            'Write clear payment schedules',
            'Draft performance metrics',
            'Create delivery requirements',
            'Write quality standards',
            'Draft acceptance criteria',
            'Create warranty conditions',
            'Write service specifications',
            'Draft compliance requirements',
            'Create reporting obligations',
            'Write audit rights',
            'Draft insurance requirements',
            'Create security protocols',
            'Write data protection terms',
            'Draft confidentiality terms',
            'Create intellectual property terms',

            // Rights & Obligations
            'Draft party responsibilities',
            'Create obligation timelines',
            'Write performance requirements',
            'Draft compliance obligations',
            'Create reporting duties',
            'Write maintenance responsibilities',
            'Draft support obligations',
            'Create training requirements',
            'Write quality assurance duties',
            'Draft documentation requirements',
            'Create communication protocols',
            'Write escalation procedures',
            'Draft review processes',
            'Create approval workflows',
            'Write change management procedures',

            // Protection Clauses
            'Draft non-compete provisions',
            'Create intellectual property protection',
            'Write data security requirements',
            'Draft privacy protection measures',
            'Create confidentiality safeguards',
            'Write trade secret protection',
            'Draft brand protection clauses',
            'Create customer protection terms',
            'Write employee protection provisions',
            'Draft asset protection measures',
            'Create reputation protection clauses',
            'Write liability protection terms',
            'Draft insurance requirements',
            'Create indemnification provisions',
            'Write risk allocation terms',

            // Enforcement & Remedies
            'Draft breach definitions',
            'Create remedy procedures',
            'Write cure period terms',
            'Draft penalty provisions',
            'Create dispute resolution steps',
            'Write arbitration procedures',
            'Draft mediation requirements',
            'Create enforcement mechanisms',
            'Write damage calculations',
            'Draft termination rights',
            'Create recovery procedures',
            'Write default consequences',
            'Draft corrective action plans',
            'Create compensation procedures',
            'Write resolution timelines',

            // Specialized Sections
            'Create change order procedures',
            'Draft milestone definitions',
            'Write success criteria',
            'Create testing procedures',
            'Draft implementation plans',
            'Write transition requirements',
            'Create exit strategies',
            'Draft renewal terms',
            'Write extension conditions',
            'Create modification procedures',
            'Draft assignment rights',
            'Write subcontracting terms',
            'Create compliance checklists',
            'Draft review procedures',
            'Write approval processes',
        ];
    }

    public function conversations()
    {
        return [
            // Document Understanding & Analysis
            'Summarize this document in simple terms',
            'What are the key points of this document?',
            'Break down this document into its main sections',
            'Explain this document as if speaking to a non-expert',
            'What are the most important terms in this document?',
            'Identify potential risks in this document',
            'List all defined terms in this document',
            'What are the critical deadlines mentioned?',
            'Highlight the main obligations for each party',
            'What action items are required by this document?',
            'Create a timeline of important dates from this document',
            'What are the compliance requirements mentioned?',
            'Explain any technical terms used in this document',
            'What are the key deliverables mentioned?',
            'Identify all stakeholders mentioned in this document',

            // Contract Analysis
            'What are the termination conditions?',
            'Explain the payment terms and conditions',
            'What are the renewal terms?',
            'List all warranties and guarantees',
            'What are the liability limitations?',
            'Explain the confidentiality provisions',
            'What are the dispute resolution procedures?',
            'Identify any force majeure clauses',
            'What are the intellectual property rights?',
            'Explain the indemnification clauses',
            'What are the insurance requirements?',
            'List all representations and warranties',
            'What are the assignment rights?',
            'Explain the governing law and jurisdiction',
            'What are the notice requirements?',

            // Contract Writing & Improvement
            'Suggest improvements for this clause',
            'Make this section more clear and concise',
            'Add standard boilerplate language for [topic]',
            'Draft a clause for intellectual property protection',
            'Create a non-compete clause',
            'Write a confidentiality agreement section',
            'Draft a termination clause',
            'Create a payment terms section',
            'Write a force majeure clause',
            'Draft a dispute resolution section',
            'Create an indemnification clause',
            'Write a warranties section',
            'Draft a liability limitation clause',
            'Create an assignment rights section',
            'Write a governing law clause',

            // Legal Terms & Definitions
            'Define this legal term in simple language',
            'Explain the meaning of this clause',
            'What does this legal jargon mean?',
            'Provide examples of how this term applies',
            'Compare these similar legal terms',
            'Explain the implications of this term',
            'What are the common interpretations of this phrase?',
            'How has this term been interpreted in court?',
            'What are the key elements of this legal concept?',
            'Explain the historical context of this term',
            'What are alternative terms for this concept?',
            'How is this term used in different jurisdictions?',
            'What are the limitations of this legal concept?',
            'Explain any exceptions to this rule',
            'How has this term evolved over time?',

            // Risk Assessment
            'Identify potential legal risks',
            'What are the compliance risks?',
            'Highlight any regulatory concerns',
            'What are the financial risks?',
            'Identify operational risks',
            'What are the reputation risks?',
            'Highlight potential conflicts of interest',
            'What are the enforcement risks?',
            'Identify any ambiguous terms',
            'What are the performance risks?',
            'Highlight any missing key provisions',
            'What are the termination risks?',
            'Identify any jurisdiction risks',
            'What are the intellectual property risks?',
            'Highlight any security risks',

            // Compliance & Regulations
            'Check for regulatory compliance',
            'Identify applicable laws and regulations',
            'What permits or licenses are required?',
            'List all compliance requirements',
            'What are the reporting obligations?',
            'Identify any regulatory deadlines',
            'What are the record-keeping requirements?',
            'List all required disclosures',
            'What are the audit requirements?',
            'Identify privacy law compliance needs',
            'What are the environmental compliance requirements?',
            'List safety and health regulations',
            'What are the employment law requirements?',
            'Identify financial reporting obligations',
            'What are the tax compliance requirements?',

            // Document Review & Quality
            'Check for inconsistencies in terminology',
            'Identify any gaps in the document',
            'Are all necessary clauses included?',
            'Check for proper cross-references',
            'Verify defined terms are used consistently',
            'Is the document structure logical?',
            'Are all exhibits and schedules referenced?',
            'Check for formatting consistency',
            'Verify signature blocks are correct',
            'Are all dates and deadlines clear?',
            'Check for numbering consistency',
            'Verify all blanks are filled in',
            'Are all parties properly identified?',
            'Check for grammatical errors',
            'Verify document version control',

            // Negotiation & Amendment
            'Suggest negotiation points',
            'What terms are typically negotiable?',
            'Draft alternative language for this clause',
            'Propose compromise language',
            'What are standard market terms?',
            'Suggest more favorable terms',
            'Draft a contract amendment',
            'Create a contract addendum',
            'Write a waiver clause',
            'Draft a contract modification',
            'Suggest alternative dispute resolution terms',
            'Propose risk allocation alternatives',
            'Draft a side letter agreement',
            'Create a contract supplement',
            'Write a contract clarification',

            // Implementation & Operation
            'Create an implementation checklist',
            'What are the next steps after signing?',
            'List required operational procedures',
            'Create a compliance timeline',
            'What systems need to be set up?',
            'List required training programs',
            'Create a monitoring plan',
            'What documentation is needed?',
            'List required notifications',
            'Create a performance tracking system',
            'What reporting is required?',
            'List required approvals',
            'Create an audit schedule',
            'What records need to be maintained?',
            'Create a contract management plan',

            // Specific Document Types
            'Analyze this employment agreement',
            'Review this lease agreement',
            'Check this non-disclosure agreement',
            'Analyze this service contract',
            'Review this software license',
            'Check this partnership agreement',
            'Analyze this purchase agreement',
            'Review this loan document',
            'Check this construction contract',
            'Analyze this merger agreement',
            'Review this franchise agreement',
            'Check this distribution agreement',
            'Analyze this consulting contract',
            'Review this settlement agreement',
            'Check this intellectual property license',

            // Industry Specific
            'Check for industry-specific regulations',
            'What are common terms in this industry?',
            'Identify industry standard practices',
            'What certifications are required?',
            'List industry-specific compliance needs',
            'What are typical warranties in this industry?',
            'Identify industry-specific risks',
            'What are standard liability terms?',
            'List required industry disclosures',
            'What are typical payment terms?',
            'Identify industry-specific insurance needs',
            'What are common exclusions?',
            'List industry-specific representations',
            'What are typical quality standards?',
            'Identify industry benchmarks',

            // Special Circumstances
            'How does this apply internationally?',
            'What changes for different jurisdictions?',
            'How to handle force majeure events?',
            'What about change in law provisions?',
            'How to address currency fluctuations?',
            'What about electronic signatures?',
            'How to handle multiple languages?',
            'What about cross-border issues?',
            'How to address pandemic-related issues?',
            'What about environmental concerns?',
            'How to handle technology changes?',
            'What about data protection issues?',
            'How to address sustainability requirements?',
            'What about social responsibility provisions?',
            'How to handle political risk?',

            // Future Planning
            'What future risks should be considered?',
            'How to plan for contract renewal?',
            'What succession planning is needed?',
            'How to handle business changes?',
            'What about technology evolution?',
            'How to plan for regulatory changes?',
            'What about market changes?',
            'How to handle organizational changes?',
            'What about industry evolution?',
            'How to plan for expansion?',
            'What about new regulations?',
            'How to handle ownership changes?',
            'What about business model changes?',
            'How to plan for new products/services?',
            'What about strategic changes?'
        ];
    }


    public function render()
    {
        return view('components.alpine-suggestion', [
            'allWritingSuggestions' => $this->writing(),  // Pass to view
            'allConversationSuggestions' => $this->conversations()  // Pass to view
        ]);
    }
}
