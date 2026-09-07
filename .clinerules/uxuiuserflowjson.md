# UX/UI & User Flow Guidelines

"Review this UI with UX best practices and common usability heuristics in mind.

[Paste UI description, screenshot link, or design details]

Provide:

1. Usability Heuristics Evaluation
   - Visibility of system status
   - Match between system and real world
   - User control and freedom
   - Consistency and standards
   - Error prevention
   - Recognition rather than recall
   - Flexibility and efficiency of use
   - Aesthetic and minimalist design
   - Help users recognize, diagnose, and recover from errors
   - Help and documentation

2. UX Best Practices Review
   - Information architecture
   - Visual hierarchy
   - Accessibility considerations
   - Responsive design
   - Interaction patterns
   - Content clarity

3. Specific Issues Identified
   - Heuristic violations
   - Usability concerns
   - Accessibility issues
   - Consistency problems
   - User experience gaps

4. Prioritized Recommendations
   - Critical issues (must fix)
   - Important improvements (should fix)
   - Nice-to-have enhancements (consider)

5. Design Strengths
   - What works well
   - Best practices followed
   - Positive patterns used

6. Improvement Suggestions
   - Specific design changes
   - Alternative approaches
   - Examples or references

Format as a structured design review with specific, actionable feedback based on UX principles.
"

"
Create a responsive design strategy for [feature/page name].

Context:

- Feature/page: [name]
- Content types: [list]
- Key interactions: [list]
- Design constraints: [describe]

Provide:

1. Device Strategy
   - Target devices (mobile, tablet, desktop)
   - Primary device (if applicable)
   - Device priorities
   - Testing approach

2. Breakpoint Strategy
   - Breakpoint definitions
   - Rationale for each breakpoint
   - Layout changes at each breakpoint
   - Component adaptations

3. Layout Adaptations
   For each breakpoint:
   - Grid system
   - Column structure
   - Spacing adjustments
   - Content prioritization

4. Component Behavior
   - How components adapt
   - When to show/hide elements
   - Navigation patterns
   - Form adaptations
   - Image/media handling

5. Typography Scaling
   - Font size adjustments
   - Line height adjustments
   - Heading hierarchy
   - Readability considerations

6. Touch & Interaction
   - Touch target sizes
   - Gesture considerations
   - Hover state alternatives
   - Input method adaptations

7. Performance Considerations
   - Image optimization
   - Asset loading strategies
   - Animation performance
   - Code splitting considerations

8. Content Strategy
   - What content to prioritize
   - What to hide or simplify
   - Progressive disclosure
   - Content hierarchy

9. Testing Plan
   - Devices to test on
   - Browsers to test
   - Key scenarios to test
   - Edge cases to consider

10. Implementation Notes
    - CSS approach (mobile-first vs desktop-first)
    - Framework considerations
    - Component library considerations
    - Maintenance approach

Format as a comprehensive responsive design strategy document.
"

"
Run a length-expansion stress test for this UI.

Context:

- Product/feature: [name]
- Key screens/components: [list]
- Target expansion factor: [30% / 50% / 70%]
- Target locales: [list]

Inputs:
[Paste UI copy, screenshots/links, and component constraints]

Provide:

1. High-Risk Strings
   - Strings most likely to expand significantly
   - Strings with variables/placeholders that can expand unpredictably

2. Breakpoints & Failure Modes
   For each screen/component, identify:
   - Where truncation will occur
   - Where wrapping will create layout shifts
   - Where buttons/inputs/nav items will overflow
   - Where table columns/cards will break

3. Recommended Fixes
   - Copy alternatives (shorter phrasing, remove redundancy)
   - Layout fixes (wrap rules, flexible widths, max-widths, responsive stacking)
   - Component fixes (icon-only variants, adaptive labels, overflow menus)
   - When truncation is acceptable vs not (and tooltip/expand patterns)

4. Test Cases
   - A checklist of “strings to test” + “screens to verify”
   - Suggested pseudo-localized examples (optional)

Format as a screen-by-screen punch list with recommended fixes.
"

"
Design the information architecture for [product/website]. Include:

1. Content Audit
   - All content types
   - Content categories
   - Content relationships
   - Content priorities

2. Navigation Structure
   - Primary navigation
   - Secondary navigation
   - Footer navigation
   - Breadcrumb structure
   - Sitemap hierarchy

3. Content Organization
   - Content grouping
   - Category structure
   - Tagging system
   - Search functionality

4. User Mental Models
   - How users think about content
   - Expected navigation patterns
   - User goals and tasks
   - Content discovery paths

5. IA Diagrams
   - Site map
   - Content hierarchy
   - Navigation flow
   - User task flows

6. Labeling System
   - Navigation labels
   - Category names
   - Content tags
   - Search terms

Format as a comprehensive IA document with diagrams and rationale.
"
