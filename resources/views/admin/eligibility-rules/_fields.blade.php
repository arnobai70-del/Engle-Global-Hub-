<div class="stu-form-grid">
    <label>Course level shown to the student<select name="study_level" required>@foreach($levels as $value => $label)<option value="{{ $value }}" @selected(old('study_level', $rule?->study_level) === $value)>{{ $label }}</option>@endforeach</select></label>
    <fieldset class="stu-full"><legend>Applies to students whose highest qualification is</legend>@foreach($qualifications as $value => $label)<label style="display:inline-flex;gap:6px;margin-right:14px"><input type="checkbox" name="qualifications[]" value="{{ $value }}" @checked(in_array($value, old('qualifications', $rule?->qualifications ?? []), true))> {{ $label }}</label>@endforeach</fieldset>
    <label>Minimum result (GPA/CGPA, on the scale of that qualification)<input type="number" name="min_gpa" step="0.01" min="0" max="5" value="{{ old('min_gpa', $rule?->min_gpa) }}" placeholder="Empty = no minimum"></label>
    <label>Minimum English score (IELTS band or equivalent)<input type="number" name="min_english" step="0.5" min="0" max="9" value="{{ old('min_english', $rule?->min_english) }}" placeholder="Empty = no English test needed"></label>
    <label>Maximum years since last study<input type="number" name="max_gap_years" min="0" max="30" value="{{ old('max_gap_years', $rule?->max_gap_years) }}" placeholder="Empty = any gap"></label>
    <label>Display order<input type="number" name="sort_order" min="0" max="65535" required value="{{ old('sort_order', $rule?->sort_order ?? 10) }}"></label>
    <label class="stu-full">Note shown with the result (English, optional)<input name="note_en" maxlength="300" value="{{ old('note_en', $rule?->note_en) }}" placeholder="For example: most institutions also ask for an interview."></label>
    <label class="stu-full">Note shown with the result (Bengali, optional)<input name="note_bn" lang="bn" maxlength="300" value="{{ old('note_bn', $rule?->note_bn) }}"></label>
    <label style="display:flex;gap:8px;align-items:center"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $rule?->is_active ?? true))> Active</label>
</div>
