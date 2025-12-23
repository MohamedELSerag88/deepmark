<?php

namespace Database\Seeders;

use App\Models\Question;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
	public function run(): void
	{
		$items = [
			[
				'question_en' => 'What’s your brand’s main point‑of‑view or core belief?',
				'question_ar' => 'ما هو الموقف أو الاعتقاد الأساسي لعلامتك؟',
				'question_type' => 'text',
				'answers' => null,
				'description_en' => 'Strong brands stand for something – what’s your big “why”?',
				'description_ar' => 'العلامات القوية تؤمن بشيء ما – ما هو سبب وجودك؟',
				'video_url' => null,
				'image_url' => null,
				'example_answer' => 'We believe great design should be accessible, human, and useful.',
				'resources' => [
					[ 'title' => 'Why it matters', 'text' => 'Customers connect with personalities, not services.' ],
					[ 'title' => 'Elements of a brand personality', 'text' => 'Sincerity, Excitement, Competence, Sophistication, Ruggedness' ],
				],
			],
			[
				'question_en' => 'Who is your primary audience and what do they value most?',
				'question_ar' => 'من هو جمهورك الأساسي وما الذي يقدرونه أكثر؟',
				'question_type' => 'text',
				'answers' => null,
				'description_en' => 'Describe the people you serve and their top priorities.',
				'description_ar' => 'صف الأشخاص الذين تخدمهم وأهم أولوياتهم.',
				'video_url' => null,
				'image_url' => null,
				'example_answer' => 'Ambitious founders who value clarity, speed, and practical outcomes.',
				'resources' => null,
			],
			[
				'question_en' => 'What transformation should customers feel after engaging with your brand?',
				'question_ar' => 'ما التغيير الذي يجب أن يشعر به العملاء بعد تفاعلهم مع علامتك؟',
				'question_type' => 'text',
				'answers' => null,
				'description_en' => 'State the before and after in emotional and practical terms.',
				'description_ar' => 'اذكر الحالة قبل وبعد بشكل عاطفي وعملي.',
				'video_url' => null,
				'image_url' => null,
				'example_answer' => 'From overwhelmed to confident and action‑ready.',
				'resources' => null,
			],
		];

		foreach ($items as $item) {
			Question::updateOrCreate(
				['question_en' => $item['question_en'], 'question_ar' => $item['question_ar']],
				[
					'question_type' => $item['question_type'],
					'answers' => $item['answers'],
					'description_en' => $item['description_en'] ?? null,
					'description_ar' => $item['description_ar'] ?? null,
					'video_url' => $item['video_url'] ?? null,
					'video_path' => $item['video_path'] ?? null,
					'image_url' => $item['image_url'] ?? null,
					'example_answer' => $item['example_answer'] ?? null,
					'resources' => $item['resources'] ?? null,
				]
			);
		}
	}
}


