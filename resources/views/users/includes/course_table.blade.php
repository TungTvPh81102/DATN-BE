@foreach ($courses as $index => $course)
<tr>
    <td>{{ $courses->firstItem() + $index }}</td>
    <td>
        <div class="d-flex">
            <img src="{{ $course->thumbnail ?? '/images/placeholder.png' }}"
                alt="thumbnail"
                class="img-thumbnail"
                style="width: 80px; height: 50px; object-fit: cover;">
            <span style="white-space: pre-line;"
                class="ms-1">{{ $course->name }}</span>
        </div>
    </td>
    @if ($roleUser == 'instructor')
        <td>{{ $course->total_student }}</td>
        <td>{{ number_format($course->total_revenue) }}
            VND</td>
        @php
            $rating =
                round($course->avg_rating * 2) /
                2;
            $fullStars = floor($rating);
            $halfStar =
                $rating - $fullStars === 0.5;
            $emptyStars =
                5 -
                $fullStars -
                ($halfStar ? 1 : 0);
        @endphp

        <td>
            @for ($i = 0; $i < $fullStars; $i++)
                <i
                    class="fas fa-star text-warning"></i>
            @endfor

            @if ($halfStar)
                <i
                    class="fas fa-star-half-alt text-warning"></i>
            @endif

            @for ($i = 0; $i < $emptyStars; $i++)
                <i
                    class="far fa-star text-warning"></i>
            @endfor
        </td>
    @else
        <td>
            <span
                class="badge bg-success text-white">Đã
                mua</span>
        </td>
        <td>
            @if ($course->progress_percent == 100)
                <span class="badge bg-primary">Hoàn
                    thành</span>
            @elseif($course->progress_percent < 100 && $course->progress_percent > 0)
                <span class="badge bg-warning">Chưa
                    hoàn
                    thành</span>
            @else
                <span class="badge bg-danger">Chưa
                    học</span>
            @endif
        </td>
        <td>{{ \Carbon\Carbon::parse($course->created_at)->format('d/m/Y H:i') }}
        </td>
    @endif
    <td>
        <a href="{{ route('admin.courses.show', $course->id) }}"
            class="btn btn-sm btn-info">
            <i class="mdi mdi-eye"></i>
        </a>
    </td>
</tr>
@endforeach