# BMAD Workflow Guide - NutriPlan

This guide outlines the recommended BMAD workflows for developing new features on the NutriPlan brownfield application.

## Project Context

- **Project:** NutriPlan
- **Type:** Brownfield (existing application)
- **Architecture:** Laravel 12 + Vue 3 with Inertia.js
- **Documentation:** Generated and available in `/docs/`

---

## For Draft PRDs: Recommended Workflow

Since you have **draft PRDs** for new features, follow this path:

### Step 1: Project Context (Brownfield Essential)

**Generate Project Context** `/bmad-bmm-generate-project-context`
- **Agent:** 📊 Mary (Business Analyst)
- **Purpose:** Scan existing codebase to generate a lean LLM-optimized `project-context.md` containing critical implementation rules, patterns, and conventions
- **Why:** Ensures all future work respects existing codebase patterns and conventions
- **Output:** `project-context.md`

### Step 2: Validate Your Draft PRDs

**Validate PRD** `/bmad-bmm-validate-prd`
- **Agent:** 📋 John (Product Manager)
- **Purpose:** Validate that your PRD is comprehensive, lean, well-organized, and cohesive
- **Why:** Ensures your draft meets BMAD standards before investing in further design work
- **Output:** PRD validation report

**If issues found → Edit PRD** `/bmad-bmm-edit-prd`
- **Agent:** 📋 John (Product Manager)
- **Purpose:** Improve and enhance an existing PRD
- **Output:** Updated PRD

---

## Full BMAD Planning Flow (After PRD Validation)

### Phase 2: Planning

#### Create UX Design (Optional but Recommended for UI Features)

**Create UX Design** `/bmad-bmm-create-ux-design`
- **Agent:** 🎨 Sally (UX Designer)
- **Purpose:** Plan UX patterns and design specifications
- **When:** Strongly recommended if a UI is a primary piece of the proposed feature
- **Output:** UX design specifications

---

### Phase 3: Solutioning

#### Create Architecture (Required)

**Create Architecture** `/bmad-bmm-create-architecture`
- **Agent:** 🏗️ Winston (Architect)
- **Purpose:** Document technical decisions and solution design
- **Output:** Architecture documentation

#### Create Epics and Stories (Required)

**Create Epics and Stories** `/bmad-bmm-create-epics-and-stories`
- **Agent:** 📋 John (Product Manager)
- **Purpose:** Break requirements into epics and implementation stories
- **Output:** Epics and stories listing

#### Check Implementation Readiness (Required)

**Check Implementation Readiness** `/bmad-bmm-check-implementation-readiness`
- **Agent:** 🏗️ Winston (Architect)
- **Purpose:** Ensure PRD, UX, Architecture, and Epics/Stories are aligned and complete
- **Output:** Readiness report

---

### Phase 4: Implementation

#### Sprint Planning (Required - Kicks Off Implementation)

**Sprint Planning** `/bmad-bmm-sprint-planning`
- **Agent:** 🏃 Bob (Scrum Master)
- **Purpose:** Generate sprint plan from epics/stories
- **Output:** Sprint status tracking

#### Story Implementation Cycle

For each story in the sprint plan:

1. **Create Story** `/bmad-bmm-create-story` - Prepare story with context
2. **Validate Story** `/bmad-bmm-validate-story` - Confirm story readiness
3. **Dev Story** `/bmad-bmm-dev-story` - Implement story and tests
4. **Code Review** `/bmad-bmm-code-review` - Review implementation
5. **If issues found → Back to Dev Story**
6. **If approved → Next Story or Epic Retrospective**

#### Optional: QA Automation

**QA Automation Test** `/bmad-bmm-qa-automate`
- **Agent:** 🧪 Quinn (QA Engineer)
- **Purpose:** Generate automated API and E2E tests
- **When:** After implementation to add test coverage

#### Epic Retrospective (Optional)

**Retrospective** `/bmad-bmm-retrospective`
- **Agent:** 🏃 Bob (Scrum Master)
- **Purpose:** Review completed work, lessons learned
- **When:** At epic end or if major issues arise

---

## Quick Alternative: For Small Features

If your draft PRDs are for **small changes** or **simple features** that fit existing patterns:

**Quick Spec** `/bmad-bmm-quick-spec`
- **Agent:** 🚀 Barry (Quick Flow Solo Dev)
- **Purpose:** Fast one-off tech specs without extensive planning
- **Use for:** Small changes, utilities, brownfield additions to well-established patterns
- **Output:** Tech spec

**Quick Dev** `/bmad-bmm-quick-dev`
- **Agent:** 🚀 Barry (Quick Flow Solo Dev)
- **Purpose:** Quick implementation without full BMAD ceremony
- **Use for:** One-off tasks, simple utilities

---

## Anytime Workflows

These can be used at any point:

| Workflow | Command | Purpose |
|----------|---------|---------|
| Document Project | `/bmad-bmm-document-project` | Analyze existing project to produce documentation |
| Index Docs | `/bmad-index-docs` | Create lightweight index for quick LLM scanning |
| Shard Document | `/bmad-shard-doc` | Split large documents into smaller files |
| Editorial Review - Prose | `/bmad-editorial-review-prose` | Review prose for clarity and tone |
| Editorial Review - Structure | `/bmad-editorial-review-structure` | Propose cuts and reorganization |
| Adversarial Review | `/bmad-review-adversarial-general` | Critical review to find issues |
| Brainstorming | `/bmad-brainstorming` | Generate diverse ideas through techniques |
| Party Mode | `/bmad-party-mode` | Orchestrate multi-agent discussions |

---

## Recommended Path Summary

```
┌─────────────────────────────────────────────────────────────────┐
│                    You Have Draft PRDs                          │
└─────────────────────────────────────────────────────────────────┘
                              ↓
        ┌─────────────────────────────────────────────────┐
        │  Generate Project Context (brownfield)          │  ← First
        │  /bmad-bmm-generate-project-context             │
        └─────────────────────────────────────────────────┘
                              ↓
        ┌─────────────────────────────────────────────────┐
        │  Validate PRD                                   │  ← For each PRD
        │  /bmad-bmm-validate-prd                         │
        └─────────────────────────────────────────────────┘
                    ↓ (if issues found)
        ┌─────────────────────────────────────────────────┐
        │  Edit PRD                                       │
        │  /bmad-bmm-edit-prd                             │
        └─────────────────────────────────────────────────┘
                              ↓
        ┌─────────────────────────────────────────────────┐
        │  Create UX Design (optional, recommended)       │
        │  /bmad-bmm-create-ux-design                     │
        └─────────────────────────────────────────────────┘
                              ↓
        ┌─────────────────────────────────────────────────┐
        │  Create Architecture (required)                 │
        │  /bmad-bmm-create-architecture                  │
        └─────────────────────────────────────────────────┘
                              ↓
        ┌─────────────────────────────────────────────────┐
        │  Create Epics and Stories (required)            │
        │  /bmad-bmm-create-epics-and-stories             │
        └─────────────────────────────────────────────────┘
                              ↓
        ┌─────────────────────────────────────────────────┐
        │  Check Implementation Readiness (required)       │
        │  /bmad-bmm-check-implementation-readiness        │
        └─────────────────────────────────────────────────┘
                              ↓
        ┌─────────────────────────────────────────────────┐
        │  Sprint Planning (kicks off implementation)      │
        │  /bmad-bmm-sprint-planning                      │
        └─────────────────────────────────────────────────┘
                              ↓
        ┌─────────────────────────────────────────────────┐
        │              Story Implementation Cycle          │
        │  Create → Validate → Dev → Code Review → ...    │
        └─────────────────────────────────────────────────┘
```

---

## Agents Reference

| Agent | Title | Role |
|-------|-------|------|
| Mary | 📊 Business Analyst | Research, analysis, project context |
| John | 📋 Product Manager | PRD creation, validation, epics/stories |
| Sally | 🎨 UX Designer | UX design and patterns |
| Winston | 🏗️ Architect | Technical architecture and decisions |
| Bob | 🏃 Scrum Master | Sprint planning, story preparation |
| Amelia | 💻 Developer | Implementation and code review |
| Quinn | 🧪 QA Engineer | Test automation |
| Barry | 🚀 Quick Flow Solo Dev | Fast specs and implementation |
| Paige | 📚 Technical Writer | Documentation and explanations |

---

## Tips

- **Run each workflow in a fresh context window** for best results
- **For brownfield projects**, always generate project context first
- **For complex features**, use the full BMAD flow
- **For simple changes**, use Quick Spec/Quick Dev
- **Validate at each checkpoint** before proceeding to next phase
- **Use the anytime workflows** as needed (documentation, reviews, brainstorming)
