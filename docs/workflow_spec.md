# MDM System Workflow Specification

## Core Principles

- **State drives UI, not forms**
- **Human priority**: Do not hold the influencer longer than necessary
- **Promoter priority**: Clicks over typing, automation over memory
- **Data priority**: Every transition must be auditable

## Static Mappings

- Engine number → Car code (1:1, immutable)
- Promoter → Car codes (pre-mapped)
- Promoter → PR Firms (pre-mapped, max 3-4)
- PR Firm → Influencers (pre-mapped list)
- Influencer → Car code (runtime only, NOT pre-mapped)

## Car Lifecycle State Machine

```
Ready → On Drive → Returned → Under Cleaning → Cleaned → POD Lineup
```

### Allowed Transitions

| From           | To             | Trigger                  |
| -------------- | -------------- | ------------------------ |
| Ready          | On Drive       | Start Drive              |
| On Drive       | Returned       | Promoter Marks Return    |
| Returned       | Under Cleaning | Post-Drive Ops Submitted |
| Under Cleaning | Cleaned        | Cleaning Complete        |
| Cleaned        | POD Lineup     | Ops Confirmation         |

### Illegal Transitions

- Ready → Returned
- On Drive → Under Cleaning
- Returned → Ready

## Key Flows

### 1. Start Drive Flow

- Trigger: Click READY car card
- Modal shows: PR Firm, Car Code, Status
- Influencer name: TYPEAHEAD from PR firm's influencer list
- On submit: Create runtime mapping, set status = On Drive, capture timestamp, lock car

### 2. Return Flow

- Promoter marks car as Returned
- Auto-capture end time
- Car becomes available for FEEDBACK ONLY

### 3. Feedback Flow (CRITICAL)

- **Audience**: Influencer (fast exit priority)
- **Timing**: BEFORE post-drive ops
- **Car selection**: Cars with status = Returned, auto-select most recent
- **Influencer**: Auto-populated from exit log (who drove this car)
- **On submit**: Store feedback, allow influencer to leave

### 4. Post-Drive Operations

- **Timing**: AFTER influencer leaves
- **Audience**: Promoter only
- **Fields**: Odometer, condition notes, photos
- **Photos**: Only allowed when status = Returned
- **On submit**: Set status = Under Cleaning

## UI Safety Guards

- Disable Start Drive when not Ready
- Disable photo upload before Returned
- Cannot skip feedback
- Auto-enforce timestamps and status locks
- Single active drive per car
