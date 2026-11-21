# Study Buddy artwork prompts — 2026-11-21

Tool: built-in `image_gen.imagegen`, transparent background enabled. No API-key/CLI fallback was used. The first generated sheet was the reference/edit target for both clothing variants. Final application assets: `public/assets/buddy/{classic,crimson,student}-{reading,resting,celebrating,greeting}.webp`.

## Base sheet — exact prompt

Create a production-quality transparent PNG sprite sheet for a website's optional study companion. Subject: Sukuna from Jujutsu Kaisen, reimagined as a cute friendly chibi anime study buddy, recognizable spiky pink hair, dark forehead and cheek markings, red eyes, white kimono with navy trim and a dark belt, fully clothed, no violence. One sheet with four equally sized cells in a precise 2x2 grid; no outlines or visible cell separators, generous transparent empty margins around each pose, no overlapping between cells. Same character scale and identity in every cell. TOP LEFT: seated reading an open blue book, calm focused expression. TOP RIGHT: resting, sitting with a little coffee cup, relaxed friendly expression. BOTTOM LEFT: celebrating academic success with both hands up, small gold stars around, cheerful confident smile. BOTTOM RIGHT: gently waving hello and holding a tiny closed book, welcoming expression, no angry or intimidating gesture. Clean hand-drawn anime linework, polished cel shading, readable at 120 px high, soft colors, alpha transparent background throughout. No text, no lettering, no watermark, no logos. Each cell should contain exactly one complete full-body character with all hair, hands, props and feet inside its own cell.

## Crimson edit — exact prompt

Edit the supplied 2x2 transparent Sukuna chibi study companion sprite sheet. Keep the exact four poses, character identity, pink hair, face markings, facial expressions, book/cup/stars, proportions, each cell placement and transparent background. Change only the white kimono fabric to a rich muted burgundy-red kimono with dark navy trim and gold sash details. This is an unlockable clothing variant for the same website character, so all four cells must have the same burgundy-red outfit. No text, no new props, no watermark. Preserve genuine alpha transparency; keep character fully inside each cell with a little transparent margin.

## Student jacket edit — exact prompt

Edit the supplied 2x2 transparent Sukuna chibi study companion sprite sheet. Keep the exact four poses, character identity, pink hair, face markings, facial expressions, book/cup/stars, proportions, each cell placement and transparent background. Change only the clothing: a dark navy-black modern student jacket over a soft grey shirt and dark trousers, subtle blue piping and small gold cuff details. This is an unlockable outfit for the same website character, so all four cells wear the same dark student jacket. No text, no new props, no watermark. Preserve genuine alpha transparency; keep character fully inside each cell with a little transparent margin.

## Delivery

The original 2×2 sheets remain in the local generated-images directory. For runtime delivery each cell was extracted, transparent margins trimmed, proportionally resized to fit 292×292, placed on a 320×320 alpha canvas, then saved as WebP quality 90. All 12 final assets are tracked in this repository, total 335,842 bytes. Pose changes use those distinct images with small CSS breathing/sway/celebration animations. The character is not a live generative chat model.
