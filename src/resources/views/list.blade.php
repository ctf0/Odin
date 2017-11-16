{{-- styles --}}
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/bulma/0.6.0/css/bulma.min.css">
<link rel="stylesheet" href="{{ asset('assets/vendor/Odin/style.css') }}"/>

<section id="app" v-cloak>
    {{-- notifications --}}
    <div class="notif-container">
        @if (session('status'))
            <my-notification
                title="Success"
                body="{{ session('status') }}"
                type="success">
            </my-notification>
        @endif

        <my-notification></my-notification>
    </div>

    {{-- Odin --}}
    <odin-comp inline-template :odin-trans="{{ json_encode(['ajax_fail'=>trans('Odin::messages.ajax_fail')]) }}">
        <div>
            {{-- overlay --}}
            <div class="odin-animated fadeIn" :class="{'shade' : selected}" @click="toggleRev()"></div>

            <div class="columns">
                <div class="column is-2"></div>
                <div class="column is-10 revisions">
                    {{-- list --}}
                    <table class="table is-narrow">
                        <tbody>
                            @foreach ($revisions as $rev)
                                @php
                                    $id = $rev->id;
                                    $user = $rev->user;
                                    $time = Carbon\Carbon::parse($rev->created_at);
                                @endphp

                                <tr @click="toggleRev('rev-{{ $id }}')" class="revisions-link" rev-id="rev-{{ $id }}">
                                    <td class="has-text-center">
                                        <figure class="image is-24x24">
                                            <img src="{{ $user->avatar }}">
                                        </figure>
                                    </td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $time->diffForHumans() }} <strong>"{{ $time->format('F j, Y @ h:i:s A') }}"</strong></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    {{-- diff --}}
                    <div class="compare-page odin-animated fadeInUp" v-show="selected">
                        {{-- close --}}
                        <div class="is-pulled-right">
                            <button class="delete" @click="toggleRev()"></button>
                        </div>

                        {{-- data --}}
                        <ul class="timeline">
                            @foreach ($revisions as $rev)
                                @php
                                    $id = $rev->id;
                                    $user = $rev->user;
                                    $time = Carbon\Carbon::parse($rev->created_at);
                                    $html = app('odin')->toHtml($rev);

                                    $class = $rev->event == 'created' ? 'is-link is-outlined' : 'is-warning';
                                @endphp

                                <li class="timeline-header" id="rev-{{ $id }}" rev-id="rev-{{ $id }}">
                                    <a href="#rev-{{ $id }}" @click.stop="goTo('#rev-{{ $id }}'), updateRev('rev-{{ $id }}')">
                                        <span class="tag is-medium is-black">{{ $time->diffForHumans() }}</span>
                                    </a>
                                </li>
                                <li class="timeline-item" rev-id="rev-{{ $id }}">
                                    <div class="timeline-marker is-icon" :class="{'is-link' : selected == 'rev-{{ $id }}'}">
                                        <template v-if="selected == 'rev-{{ $id }}'">
                                            <i class="fa fa-flag"></i>
                                        </template>
                                    </div>
                                    <div class="timeline-content">
                                        <div class="heading">
                                            <p><span class="title">{{ $rev->event }}</span></p>
                                            <p><span class="subtitle is-6">{{ $time->format('F j, Y @ h:i:s A') }}</span></p>
                                            <p><small class="subtitle is-6">By</small> <span class="subtitle is-5">{{ $user->name }}</span></p>
                                        </div>

                                        <div>
                                            {{-- data --}}
                                            <section class="compare-page__body">
                                                @if ($html)
                                                    {!! $html !!}
                                                @else
                                                    <p class="title is-5 notification is-info is-marginless">
                                                        {{ trans('Odin::messages.no_diff') }}
                                                    </p>
                                                @endif
                                            </section>

                                            {{-- ops --}}
                                            @if (count($revisions) > 1)
                                                <div class="compare-page__footer">

                                                    @if ($rev->event == 'created')
                                                        <p class="title is-6">
                                                            * {{ trans('Odin::messages.reset_data') }}
                                                        </p>
                                                    @endif

                                                    <div class="level">
                                                        @if ($html)
                                                            <div class="level-left"></div>
                                                            <div class="level-right">
                                                                <div class="level-item">
                                                                    {{ Form::open(['route' => ['odin.restore', $id], 'method' => 'POST']) }}
                                                                        <button class="button {{ $class }}">{{ trans('Odin::messages.res') }}</button>
                                                                    {{ Form::close() }}
                                                                </div>

                                                                @if ($rev->event !== 'created')
                                                                    <div class="level-item">
                                                                        {{ Form::open([
                                                                            'route' => ['odin.remove', $id],
                                                                            'method' => 'DELETE',
                                                                            'data-id'=> "rev-$id",
                                                                            '@submit.prevent'=>'removeRev($event)'
                                                                        ]) }}
                                                                            <button class="button is-danger">{{ trans('Odin::messages.del') }}</button>
                                                                        {{ Form::close() }}
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        @else
                                                            <div class="level-right"></div>
                                                            <div class="level-left">
                                                                {{ Form::open([
                                                                    'route' => ['odin.remove', $id],
                                                                    'method' => 'DELETE',
                                                                    'data-id'=> "rev-$id",
                                                                    '@submit.prevent'=>'removeRev($event)'
                                                                ]) }}
                                                                    <button class="button is-danger">{{ trans('Odin::messages.del') }}</button>
                                                                {{ Form::close() }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </odin-comp>
</section>

{{-- Footer --}}
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
<script src="//code.jquery.com/jquery-3.2.1.min.js"></script>
<script src="{{ asset('path/to/app.js') }}"></script>
