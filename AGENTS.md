# developer-plenger skill pack

This file registers the `developer-plenger` skill pack for every agent working in
this repository. `/developer-plenger:init` writes and maintains it, and its
content is the same in every project: it describes how the pack's skills work,
never what this project is. Project-specific facts — the stack, the conventions,
the purpose — live in `context.md`, written by `/developer-plenger:plan`.

## Workflow

The registered chain is:

```text
INIT → PLAN → SLICE → EXEC → (REVIEW ∥ CHECK) → FIX
```

- REVIEW and CHECK are siblings of EXEC, never a chain: the edges are `exec → review` and `exec → check`, never `exec → review → check`. Either may run first, either may run without the other, and both may run on the same task.
- CHECK runs per finished slice by default, plus whenever a single task needs a test result before anyone can trust it.
- PLAN runs once per plan: every run creates `specs/NN-<plan-slug>/` with its own `spec.md` and its own `slice/` folder. The first run is the initial build; every later run is a feature or a change of requirements. Plans accumulate; none of them is edited by a later one.
- FIX repairs what review and check surfaced, then resets the checkboxes its change invalidated.

Invoke with `/developer-plenger:init`, `/developer-plenger:plan`, `/developer-plenger:slice NN-<plan-slug>`, `/developer-plenger:exec NN-<plan-slug>/NN-pK`, `/developer-plenger:review NN-<plan-slug>/NN-pK`, `/developer-plenger:check NN-<plan-slug>/NN-pK`, `/developer-plenger:fix NN-<plan-slug>/NN-pK` — or `TASK-NNN` in place of `NN-pK` to target one task. Task and stage IDs restart per plan, so the plan folder is part of every invocation that names a task. Plugin skills are namespaced by the plugin name; the bare names `/init`, `/plan`, and `/review` are claimed by Claude Code's own built-ins, so the namespaced form is required for those and is safe for all seven.

## Artifacts

Every hand-off is a file in the repository, never chat state. Nothing important lives in the conversation.

| Artifact | Written by | Location |
|---|---|---|
| Pack registration | `/developer-plenger:init` | `AGENTS.md` |
| Project context | `/developer-plenger:init` creates it, `/developer-plenger:plan` fills it | `context.md` |
| Specification | `/developer-plenger:plan` | `specs/NN-<plan-slug>/spec.md` |
| Stage plan | `/developer-plenger:slice` | `specs/NN-<plan-slug>/slice/NN-pK/tasks.md` |
| Review | `/developer-plenger:review` | `specs/NN-<plan-slug>/reviews/TASK-NNN.md` |
| Check | `/developer-plenger:check` | `specs/NN-<plan-slug>/checks/TASK-NNN.md` |
| Fix | `/developer-plenger:fix` | `specs/NN-<plan-slug>/fixes/TASK-NNN.md` |

`specs/` holds one numbered folder per plan: `specs/01-<plan-slug>/` from the first `/plan`, `specs/02-<plan-slug>/` from the second, and so on. Each plan folder is self-contained — its `spec.md`, its `slice/` stages, and the reviews, checks and fixes of the work it produced. Adding a plan appends the next number; existing plan folders are never renumbered or rewritten.

**Task and stage IDs restart in every plan folder.** `TASK-001` and `slice/01-p0/` exist once per plan, so a bare ID never identifies a task on its own — every invocation names the plan, and the plan folder is where that task's reviews, checks and fixes are written.

The skill that writes an artifact also creates its directory: `/init` creates `context.md` at the root and no directory at all, `/plan` creates `specs/NN-<plan-slug>/` with its `spec.md`, `/slice` creates that plan's `slice/`, `/review` creates its `reviews/`, `/check` creates its `checks/`, and `/fix` creates its `fixes/`.

Task IDs are `TASK-NNN`, zero-padded to three digits, restarting at `TASK-001` inside every plan folder. `FR-NNN` and `US-NNN` requirement and story IDs restart the same way, because a plan's `spec.md` states only that plan's delta and the plan folder is the namespace. Stage IDs come from the folder name: `02-p1` is `specs/NN-<plan-slug>/slice/02-p1/`. Findings are numbered inside their own review document — `FINDING-001` in `specs/NN-<plan-slug>/reviews/TASK-003.md` — and referenced elsewhere as `NN-<plan-slug>/TASK-003#FINDING-001` when the plan is not implicit.

## Skill Rules

One entry per skill. All seven, in this shape.

### INIT

Register the pack and create the empty repository index. Writes both root files and asks nothing.

- Invocation: `/developer-plenger:init`
- Input: the repository's `AGENTS.md` and `context.md`, if they already exist
- Produces: `AGENTS.md`, and `context.md` with its thirteen sections and `Current Phase: INIT`

### PLAN

Write one plan's specification. Each run is a new plan; earlier plans are never edited.

- Invocation: `/developer-plenger:plan <idea>`
- Input: the user's idea, `context.md`, every existing plan's `spec.md`, and the existing codebase
- Produces: `specs/NN-<plan-slug>/spec.md`, a new plan folder, and the project's sections in `context.md`

### SLICE

Cut one plan's specification into its ordered development stages and tasks.

- Invocation: `/developer-plenger:slice NN-<plan-slug>`, or bare `/slice` when only one plan is unsliced
- Input: `specs/NN-<plan-slug>/spec.md` and `context.md`
- Produces: `specs/NN-<plan-slug>/slice/NN-pK/tasks.md`, and that plan's task ID sequence

### EXEC

Implement a task, and check its `Implemented` box only once the code actually runs. Never check a box the code does not earn.

- Invocation: `/developer-plenger:exec NN-<plan-slug>/NN-pK` or `/developer-plenger:exec NN-<plan-slug>/TASK-NNN`
- Input: the slice file, that plan's `spec.md`, `context.md`, and the existing source
- Produces: source code; checks `Implemented`

### REVIEW

Review an implementation against its spec and acceptance criteria, recording one numbered finding per problem. Never edits the code it reviews.

- Invocation: `/developer-plenger:review NN-<plan-slug>/NN-pK` or `/developer-plenger:review NN-<plan-slug>/TASK-NNN`
- Input: the task, the spec section it came from, its acceptance criteria, and the implementation
- Produces: `specs/NN-<plan-slug>/reviews/TASK-NNN.md`; checks `Reviewed` when no unresolved finding of Medium or higher remains

### CHECK

Detect the testing ecosystem instead of assuming one, then prove each acceptance criterion. Never checks `Tested` without passing evidence.

- Invocation: `/developer-plenger:check NN-<plan-slug>/NN-pK` or `/developer-plenger:check NN-<plan-slug>/TASK-NNN`
- Input: the task's acceptance criteria and the implementation
- Produces: `specs/NN-<plan-slug>/checks/TASK-NNN.md`; checks `Tested` when every acceptance criterion has passing evidence

### FIX

Repair what review and check surfaced, then reset the boxes that evidence no longer covers. Never checks a box; reset always means `- [ ]`.

- Invocation: `/developer-plenger:fix NN-<plan-slug>/TASK-NNN`, or `/developer-plenger:fix NN-<plan-slug>/TASK-NNN#FINDING-NNN`
- Input: `specs/NN-<plan-slug>/reviews/TASK-NNN.md` and/or `specs/NN-<plan-slug>/checks/TASK-NNN.md`
- Produces: fixes plus `specs/NN-<plan-slug>/fixes/TASK-NNN.md`; resets the boxes its change invalidated

## Task State

Task state lives in the three checkboxes on the task, in the slice file that holds it: `Implemented`, `Reviewed`, `Tested`. There is never a `status:` field. One owner per box:

- `Implemented` is set by `/developer-plenger:exec`.
- `Reviewed` is set by `/developer-plenger:review`, only when no unresolved finding of severity Medium or higher remains.
- `Tested` is set by `/developer-plenger:check`, only when every acceptance criterion has passing evidence.

`DONE` is derived: all three boxes checked. It is never stored. `/developer-plenger:fix` resets the boxes its change invalidated; a code change always resets `Reviewed` and `Tested`.
