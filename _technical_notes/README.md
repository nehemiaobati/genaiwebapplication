# Technical Notes Directory

## Purpose

This directory (`_technical_notes/`) serves as a dedicated knowledge base for storing detailed technical documentation, research findings, and "forensic" explanations of complex implementations within the codebase.

It is designed to bridge the gap between code comments (which should be brief) and external documentation (which can be disconnected).

## Audience

1.  **Human Developers**: To understand the deep "why" behind specific implementations, especially those involving workarounds, hacks, or complex integrations (e.g., PHPWord HTML parsing).
2.  **AI Agents**: To provide critical context that prevents "fixing" necessary workarounds or misunderstanding architectural decisions during future refactoring or feature development.

## Usage Procedure

### When to Add a Note

- When you implement a non-obvious workaround for a library bug (e.g., PHPWord's ampersand handling).
- When you make a significant architectural decision that requires detailed justification.
- When you conduct research or experiments (e.g., comparing libraries) and want to preserve the findings.

### How to Create a Note

1.  Create a new file in this directory with a descriptive name (e.g., `phpword_workarounds.txt`, `payment_gateway_logic.md`).
2.  Structure the note with the following sections:
    - **Context**: What were you trying to do?
    - **The Problem**: What specific issues or bugs did you encounter?
    - **The Solution**: Detailed explanation of the fix or implementation.
    - **Testing Methodology**: How can this be verified? (Include reproduction scripts or commands).
3.  **Important**: If the solution involves "hacks" (like Regex pre-processing), explicitly state _why_ they are necessary so they aren't removed by future cleanup efforts.

### Maintenance

- Keep these notes updated if the underlying code changes significantly.
- If a library update resolves a documented issue, update the note to reflect that the workaround is no longer needed (or delete it).

## Git Status

This directory is currently **.gitignored** to prevent cluttering the main repository with transient notes, but it should be maintained locally or backed up as part of the project's internal knowledge base.
